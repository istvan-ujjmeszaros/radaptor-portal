const { execFileSync } = require("node:child_process");
const path = require("node:path");
const { test, expect } = require("@playwright/test");

const MAILPIT_BASE_URL = process.env.E2E_MAILPIT_URL || "http://localhost:8036";
const PORTAL_BASE_URL = process.env.E2E_BASE_URL || "http://localhost:8020";
const PORTAL_ROOT = path.resolve(__dirname, "..", "..");

test("request access submits, queues email, and confirms via Mailpit", async ({ page, request }) => {
	drainQueue();
	await clearMailpitInbox(request);

	const uniqueEmail = `portal-access-${Date.now()}@example.test`;

	await page.goto("/request-access/");
	await expectNoLibraryErrors(page);
	await page.getByLabel("Email address").fill(uniqueEmail);
	await page.getByLabel("Keep me posted about Radaptor Platform updates as well.").check();
	await page.getByRole("button", { name: "Request access" }).click();

	await expect(page).toHaveURL(/\/request-access\/$/);
	await expect(page.getByText("Check your inbox")).toBeVisible();
	await expect(page.getByText("Waiting for confirmation")).toBeVisible();
	await expect(page.getByLabel("Email address")).toHaveCount(0);
	await expectNoLibraryErrors(page);

	const firstConfirmationUrl = await waitForConfirmationUrl(request, uniqueEmail);
	await clearMailpitInbox(request);

	await page.goto("/request-access/");
	await expect(page.getByLabel("Email address")).toHaveCount(1);
	await page.getByLabel("Email address").fill(uniqueEmail);
	await page.getByRole("button", { name: "Request access" }).click();

	await expect(page).toHaveURL(/\/request-access\/$/);
	await expect(page.getByText("Check your inbox")).toBeVisible();
	await expect(page.getByText("Waiting for confirmation")).toBeVisible();
	await expect(page.getByLabel("Email address")).toHaveCount(0);
	await expectNoLibraryErrors(page);

	const confirmationUrl = await waitForConfirmationUrl(request, uniqueEmail);
	expect(confirmationUrl).not.toBe(firstConfirmationUrl);
	await clearMailpitInbox(request);
	await page.goto(confirmationUrl);

	await expect(page).toHaveURL(/\/request-access\/$/);
	await expect(page.getByText("Your request is confirmed")).toBeVisible();
	await expect(page.getByText("Confirmed for follow-up")).toBeVisible();
	await expect(page.getByLabel("Email address")).toHaveCount(0);
	await expectNoLibraryErrors(page);

	await page.goto("/request-access/");
	await expect(page.getByLabel("Email address")).toHaveCount(1);
	await page.getByLabel("Email address").fill(uniqueEmail);
	await page.getByRole("button", { name: "Request access" }).click();

	await expect(page).toHaveURL(/\/request-access\/$/);
	await expect(page.getByText("Check your inbox")).toBeVisible();
	await expect(page.getByText("Waiting for confirmation")).toBeVisible();
	await expect(page.getByLabel("Email address")).toHaveCount(0);
	await expectNoLibraryErrors(page);

	const repeatMessage = await waitForMessage(request, uniqueEmail, {
		subjectIncludes: "We already have your early access request",
	});
	expect(JSON.stringify(repeatMessage)).toContain("We already have a confirmed early-access request");
	expect(JSON.stringify(repeatMessage)).toContain("Recorded at:");
	expect(JSON.stringify(repeatMessage)).not.toContain("context=portalAccessRequest");
});

async function waitForConfirmationUrl(request, uniqueEmail) {
	const detailPayload = await waitForMessage(request, uniqueEmail);
	const confirmationUrl = extractConfirmationUrl(detailPayload);

	if (confirmationUrl) {
		return confirmationUrl;
	}

	throw new Error(`Timed out waiting for confirmation email for ${uniqueEmail}`);
}

async function waitForMessage(request, uniqueEmail, options = {}) {
	const subjectIncludes = options.subjectIncludes || null;

	for (let attempt = 0; attempt < 10; attempt += 1) {
		execFileSync("./radaptor.sh", ["emailqueue:run", "--once", "--json"], {
			cwd: PORTAL_ROOT,
			stdio: "pipe",
		});

		const listResponse = await request.get(`${MAILPIT_BASE_URL}/api/v1/messages`);
		expect(listResponse.ok()).toBeTruthy();
		const listPayload = await listResponse.json();
		const messages = Array.isArray(listPayload) ? listPayload : (listPayload.messages || []);
		const matchingMessage = messages.find((message) => {
			if (!JSON.stringify(message).includes(uniqueEmail)) {
				return false;
			}

			if (subjectIncludes === null) {
				return true;
			}

			return JSON.stringify(message).includes(subjectIncludes);
		});

		if (matchingMessage) {
			const messageId = matchingMessage.ID || matchingMessage.id;
			const detailResponse = await request.get(`${MAILPIT_BASE_URL}/api/v1/message/${messageId}`);
			expect(detailResponse.ok()).toBeTruthy();
			return detailResponse.json();
		}

		await new Promise((resolve) => setTimeout(resolve, 250));
	}

	throw new Error(`Timed out waiting for confirmation email for ${uniqueEmail}`);
}

function drainQueue(maxRuns = 10) {
	for (let attempt = 0; attempt < maxRuns; attempt += 1) {
		execFileSync("./radaptor.sh", ["emailqueue:run", "--once", "--json"], {
			cwd: PORTAL_ROOT,
			stdio: "pipe",
		});
	}
}

async function clearMailpitInbox(request) {
	const response = await request.delete(`${MAILPIT_BASE_URL}/api/v1/messages`);
	expect([200, 204]).toContain(response.status());
}

function extractConfirmationUrl(detailPayload) {
	const candidates = [
		detailPayload.Text || "",
		detailPayload.HTML || "",
		JSON.stringify(detailPayload),
	];
	const normalizedPortalBaseUrl = PORTAL_BASE_URL.replace(/\/$/, "");
	const escapedPortalBaseUrl = normalizedPortalBaseUrl.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
	const confirmationUrlPattern = new RegExp(`${escapedPortalBaseUrl}\\/\\?[^\\s"'<>]+`, "gi");

	for (const candidate of candidates) {
		const urls = candidate.match(confirmationUrlPattern) || [];

		for (const rawUrl of urls) {
			const normalizedUrl = rawUrl
				.replace(/&amp;/gi, "&")
				.replace(/\\u0026/gi, "&");

			try {
				const parsed = new URL(normalizedUrl);
				const context = parsed.searchParams.get("context");
				const event = parsed.searchParams.get("event");
				const token = parsed.searchParams.get("token");

				if (context === "portalAccessRequest" && event === "confirm" && /^[a-f0-9]+$/i.test(token || "")) {
					return parsed.toString();
				}
			} catch {
				// Ignore malformed URL candidates and continue scanning.
			}
		}
	}

	return null;
}

async function expectNoLibraryErrors(page) {
	await expect(page.locator("body")).not.toContainText("Unknown library:");
}
