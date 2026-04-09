const { test, expect } = require("@playwright/test");

test("bootstrap admin can log in and open the admin dashboard", async ({ page }) => {
	const username = process.env.E2E_BOOTSTRAP_ADMIN_USERNAME || "admin";
	const password = process.env.E2E_BOOTSTRAP_ADMIN_PASSWORD || "admin123456";

	await page.goto("/login.html");
	await expect(page).toHaveURL(/\/login\.html$/);

	await page.getByLabel(/username/i).fill(username);
	await page.getByLabel(/password/i).fill(password);
	await page.getByRole("button").click();

	await page.goto("/admin/index.html");
	await expect(page).toHaveURL(/\/admin\/index\.html$/);
	await expect(page).toHaveTitle(/Administration - Radaptor Portal/);
	await expect(page.locator("body")).toBeVisible();
	await expect(page.getByText("Welcome to Radaptor Portal")).toBeVisible();

	await page.goto("/admin/users/");
	await expect(page).toHaveURL(/\/admin\/users\/$/);
	await expect(page.locator("body")).toContainText("New user");

	await page.goto("/admin/roles/");
	await expect(page).toHaveURL(/\/admin\/roles\/$/);
	await expect(page.locator("body")).toContainText("New role");

	await page.goto("/");
	await expect(page).toHaveURL(/\/$/);
	await expect(page.getByText("Build widget-driven applications")).toBeVisible();
	await expect(page.locator("body")).not.toContainText("Unknown library:");

	await page.goto("/request-access/");
	await expect(page).toHaveURL(/\/request-access\/$/);
	await expect(page.getByText("Request early access")).toBeVisible();
	await expect(page.locator("body")).not.toContainText("Unknown library:");

	await enablePublicEditMode(page, "/");
	await expect(page.locator(".admin-dropdown-container")).toHaveCount(1);
	await page.locator(".admin-dropdown-container button").click();
	await expect(page.locator(".admin-dropdown-container .dropdown-menu.show")).toHaveCount(1);
	await expect(page.locator(".admin-dropdown-container .dropdown-menu")).toContainText("SEO");
	await expect(page.locator(".admin-dropdown-container .dropdown-menu")).toContainText("Edit mode");
	await expect(page.locator("body")).not.toContainText("Unknown library:");

	await enablePublicEditMode(page, "/request-access/");
	await expect(page.locator(".admin-dropdown-container")).toHaveCount(1);
	await page.locator(".admin-dropdown-container button").click();
	await expect(page.locator(".admin-dropdown-container .dropdown-menu.show")).toHaveCount(1);
	await expect(page.locator(".admin-dropdown-container .dropdown-menu")).toContainText("SEO");
	await expect(page.locator(".admin-dropdown-container .dropdown-menu")).toContainText("Edit mode");
	await expect(page.locator("body")).not.toContainText("Unknown library:");
});

async function enablePublicEditMode(page, refererPath) {
	const refererUrl = new URL(refererPath, process.env.E2E_BASE_URL || "http://localhost:8020").toString();
	await page.goto(`/?context=page_editmode&event=switch&set=1&referer=${encodeURIComponent(refererUrl)}`);
	await page.waitForLoadState("networkidle");
}
