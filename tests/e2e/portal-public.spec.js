const { test, expect } = require("@playwright/test");

test("public portal routes render the lean demo surface", async ({ page }) => {
	await page.goto("/");
	await expect(page.getByText("Build widget-driven applications")).toBeVisible();
	await expect(page.getByRole("heading", { name: "SDUI built in" })).toBeVisible();
	await expect(page.locator('a[href="/comparison/"]').filter({ hasText: "Technical Comparison" }).first()).toBeVisible();
	await expect(page.getByText("Admin Login")).toHaveCount(0);
	await expect(page.getByText("Your portal skeleton is installed and ready.")).toHaveCount(0);
	await expectNoLibraryErrors(page);

	await page.goto("/comparison/");
	await expect(page.getByText("Comparison at a glance")).toBeVisible();
	await expect(page.getByText("Request entry point")).toBeVisible();
	await expectNoLibraryErrors(page);

	await page.goto("/request-access/");
	await expect(page.getByText("Request early access")).toBeVisible();
	await expect(page.getByLabel("Email address")).toBeVisible();
	await expect(page.getByText("Admin Login")).toHaveCount(0);
	await expect(page.getByRole("button", { name: "Request access" })).toBeEnabled();
	await expectNoLibraryErrors(page);
});

async function expectNoLibraryErrors(page) {
	await expect(page.locator("body")).not.toContainText("Unknown library:");
}
