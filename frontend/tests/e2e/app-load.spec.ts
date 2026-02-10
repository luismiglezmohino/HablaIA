import { test, expect } from '@playwright/test'

test.describe('App Load', () => {
  test('should display the app header with title', async ({ page }) => {
    await page.goto('/')

    const heading = page.getByRole('heading', { level: 1 })
    await expect(heading).toBeVisible()
    await expect(heading).toContainText('HablaIA')
  })

  test('should load and display categories', async ({ page }) => {
    await page.goto('/')

    const tablist = page.getByRole('tablist')
    await expect(tablist).toBeVisible()

    const tabs = tablist.getByRole('tab')
    const count = await tabs.count()
    expect(count).toBeGreaterThanOrEqual(10)
  })

  test('should have accessible skip link', async ({ page }) => {
    await page.goto('/')

    const skipLink = page.locator('a.skip-link')
    await skipLink.focus()
    await expect(skipLink).toBeFocused()
    await expect(skipLink).toContainText('Ir al contenido principal')
  })
})
