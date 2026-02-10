import { test, expect } from '@playwright/test'

test.describe('Phrase Limits', () => {
  test('should limit selection to 10 pictograms', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    // Select a category with enough pictograms
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    const pictograms = grid.getByRole('button')
    const count = await pictograms.count()
    const clickCount = Math.min(count, 10)

    // Select up to 10 pictograms
    for (let i = 0; i < clickCount; i++) {
      await pictograms.nth(i).click()
    }

    // Counter should show 10/10
    await expect(page.getByText(`${clickCount}/10`)).toBeVisible()

    // If we have more than 10 pictograms, the 11th should be disabled
    if (count > 10) {
      await expect(pictograms.nth(10)).toBeDisabled()
    }
  })

  test('should re-enable pictograms after removing from phrase bar', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    const pictograms = grid.getByRole('button')
    const count = await pictograms.count()
    const clickCount = Math.min(count, 10)

    // Fill to max
    for (let i = 0; i < clickCount; i++) {
      await pictograms.nth(i).click()
    }

    // Remove one chip
    await page.getByTestId('remove-chip').first().click()

    // Counter should now show 9/10
    await expect(page.getByText(`${clickCount - 1}/10`)).toBeVisible()

    // Pictograms should be enabled again
    await expect(pictograms.first()).toBeEnabled()
  })
})
