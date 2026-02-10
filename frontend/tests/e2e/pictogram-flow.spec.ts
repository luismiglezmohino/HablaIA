import { test, expect } from '@playwright/test'

test.describe('Pictogram Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()
  })

  test('should display pictograms when a category is clicked', async ({ page }) => {
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    const pictograms = grid.getByRole('button')
    await expect(pictograms.first()).toBeVisible()
  })

  test('should add pictogram to phrase bar when clicked', async ({ page }) => {
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    await grid.getByRole('button').first().click()

    await expect(page.getByTestId('generate-btn')).toBeVisible()
    await expect(page.getByTestId('clear-btn')).toBeVisible()
    await expect(page.getByTestId('remove-chip')).toBeVisible()
  })

  test('should generate phrases after selecting pictograms', async ({ page }) => {
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    const pictograms = grid.getByRole('button')
    await pictograms.nth(0).click()
    await pictograms.nth(1).click()

    await page.getByTestId('generate-btn').click()

    const phraseList = page.getByRole('list')
    await expect(phraseList).toBeVisible({ timeout: 10000 })

    const items = phraseList.getByRole('listitem')
    const count = await items.count()
    expect(count).toBeGreaterThanOrEqual(1)
  })

  test('should remove a chip when remove button is clicked', async ({ page }) => {
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    await grid.getByRole('button').first().click()
    await expect(page.getByTestId('remove-chip')).toBeVisible()

    await page.getByTestId('remove-chip').click()

    await expect(page.getByText('Selecciona pictogramas para construir una frase')).toBeVisible()
  })

  test('should clear all chips when clear button is clicked', async ({ page }) => {
    await page.getByRole('tab').first().click()

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible()

    const pictograms = grid.getByRole('button')
    await pictograms.nth(0).click()
    await pictograms.nth(1).click()

    await page.getByTestId('clear-btn').click()

    await expect(page.getByText('Selecciona pictogramas para construir una frase')).toBeVisible()
  })
})
