import { test, expect } from '@playwright/test'

test.describe('Search', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()
  })

  test('should search pictograms by query', async ({ page }) => {
    const searchBox = page.getByRole('searchbox')
    await searchBox.fill('agua')

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible({ timeout: 5000 })

    const results = grid.getByRole('button')
    await expect(results.first()).toBeVisible()
  })

  test('should search, select result, and generate phrase', async ({ page }) => {
    const searchBox = page.getByRole('searchbox')
    await searchBox.fill('agua')

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible({ timeout: 5000 })

    await grid.getByRole('button').first().click()
    await expect(page.getByTestId('generate-btn')).toBeVisible()

    await page.getByTestId('generate-btn').click()

    const phraseList = page.getByRole('list')
    await expect(phraseList).toBeVisible({ timeout: 10000 })

    const items = phraseList.getByRole('listitem')
    const count = await items.count()
    expect(count).toBeGreaterThanOrEqual(1)
  })
})

test.describe('Keyboard Navigation', () => {
  test('should navigate categories with arrow keys', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    const firstTab = page.getByRole('tab').first()
    await firstTab.click()
    await expect(firstTab).toHaveAttribute('aria-selected', 'true')

    await page.keyboard.press('ArrowRight')

    const secondTab = page.getByRole('tab').nth(1)
    await expect(secondTab).toHaveAttribute('aria-selected', 'true')
    await expect(firstTab).toHaveAttribute('aria-selected', 'false')
  })
})
