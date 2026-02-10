import { test, expect } from '@playwright/test'

test.describe('Error Handling', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()
  })

  test('should show error message when phrase generation fails', async ({ page }) => {
    // Intercept the generate API call and return 500
    await page.route('**/api/phrases/generate', (route) =>
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: 'Internal Server Error' }),
      }),
    )

    // Select a category and a pictogram
    await page.getByRole('tab').first().click()
    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    await grid.getByRole('button').first().click()

    // Click generate
    await page.getByTestId('generate-btn').click()

    // Error alert should appear
    const errorAlert = page.getByRole('alert')
    await expect(errorAlert).toBeVisible({ timeout: 10000 })
  })

  test('should show retry button after error', async ({ page }) => {
    // First call fails, second succeeds
    let callCount = 0
    await page.route('**/api/phrases/generate', (route) => {
      callCount++
      if (callCount === 1) {
        return route.fulfill({
          status: 500,
          contentType: 'application/json',
          body: JSON.stringify({ error: 'Internal Server Error' }),
        })
      }
      return route.continue()
    })

    await page.getByRole('tab').first().click()
    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    await grid.getByRole('button').first().click()
    await page.getByTestId('generate-btn').click()

    // Wait for error
    await expect(page.getByRole('alert')).toBeVisible({ timeout: 10000 })

    // Generate button should now show "Reintentar"
    const retryBtn = page.getByTestId('generate-btn')
    await expect(retryBtn).toContainText('Reintentar')

    // Click retry — should succeed this time
    await retryBtn.click()

    const phraseList = page.getByRole('list')
    await expect(phraseList).toBeVisible({ timeout: 10000 })
  })
})
