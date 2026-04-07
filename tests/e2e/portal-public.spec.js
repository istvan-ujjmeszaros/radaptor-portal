const { test, expect } = require("@playwright/test");

test("public portal routes render the lean demo surface", async ({ page }) => {
	await page.goto("/");
	await expect(page.getByText("Build widget-driven applications")).toBeVisible();
	await expect(page.locator('a[href="/comparison/"]').filter({ hasText: "Technical Comparison" }).first()).toBeVisible();

	await page.goto("/comparison/");
	await expect(page.getByText("Comparison at a glance")).toBeVisible();
	await expect(page.getByText("Request entry point")).toBeVisible();

	await page.goto("/request-access/");
	await expect(page.getByText("Request access without pretending the backend exists")).toBeVisible();
	await expect(page.getByLabel("Email address")).toBeVisible();
	await expect(page.getByRole("button", { name: "Email delivery coming soon" })).toBeDisabled();
});
