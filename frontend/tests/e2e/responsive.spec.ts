import { test, expect } from '@playwright/test'

test.describe('Responsive - Mobile Portrait (375x667)', () => {
  test.use({ viewport: { width: 375, height: 667 } })

  test('should show mobile layout', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    // Mobile search input visible in header
    await expect(page.locator('#mobile-search')).toBeVisible()

    // Desktop search hidden
    await expect(page.locator('#search-pictograms')).not.toBeVisible()

    // Title hidden on mobile (sr-only: 1x1px clipped)
    await expect(page.getByRole('heading', { level: 1 })).toHaveCSS('width', '1px')
  })

  test('should complete full flow on mobile', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    // Search using mobile input
    await page.locator('#mobile-search').fill('agua')

    const grid = page.getByRole('grid', { name: 'Pictogramas' })
    await expect(grid).toBeVisible({ timeout: 5000 })

    await grid.getByRole('button').first().click()
    await page.getByTestId('generate-btn').click()

    const phraseList = page.getByRole('list')
    await expect(phraseList).toBeVisible({ timeout: 10000 })
    const items = phraseList.getByRole('listitem')
    const count = await items.count()
    expect(count).toBeGreaterThanOrEqual(1)
  })
})

test.describe('Responsive - Mobile Landscape (667x375)', () => {
  test.use({ viewport: { width: 667, height: 375 } })

  test('should show landscape blocker on mobile', async ({ page }) => {
    await page.goto('/')

    // Landscape blocker should be visible (max-height: 500px + landscape)
    const blocker = page.getByRole('alert')
    await expect(blocker).toBeVisible()
    await expect(blocker).toContainText('Gira tu dispositivo')
  })
})

test.describe('Responsive - Tablet Portrait (768x1024)', () => {
  test.use({ viewport: { width: 768, height: 1024 } })

  test('should show tablet portrait layout', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    // Desktop search visible below PhraseBar
    await expect(page.locator('#search-pictograms')).toBeVisible()

    // Title visible
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

    // CategoryBar visible with names
    const tabs = page.getByRole('tab')
    const tabCount = await tabs.count()
    expect(tabCount).toBeGreaterThanOrEqual(10)
  })
})

test.describe('Responsive - Tablet Landscape (1024x768)', () => {
  test.use({ viewport: { width: 1024, height: 768 } })

  test('should show compact tablet landscape layout', async ({ page }) => {
    await page.goto('/')

    // Compact category icons in header (tablet-landscape-show)
    // The header nav with aria-label="Categorías" should be visible
    const headerNav = page.locator('header nav[aria-label="Categorías"]')
    await expect(headerNav).toBeVisible()

    // Mobile search in header (tablet-landscape-show)
    await expect(page.locator('#mobile-search')).toBeVisible()

    // Full CategoryBar hidden (tablet-landscape-hide)
    // The standalone CategoryBar nav below PhraseBar should be hidden
    const standaloneNav = page.locator('div.tablet-landscape-hide nav[aria-label="Categorías"]')
    await expect(standaloneNav).not.toBeVisible()

    // Desktop SearchBar hidden (tablet-landscape-hide)
    await expect(page.locator('#search-pictograms')).not.toBeVisible()
  })
})

test.describe('Responsive - Desktop (1280x720)', () => {
  test.use({ viewport: { width: 1280, height: 720 } })

  test('should show full desktop layout', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('tablist')).toBeVisible()

    // All elements visible
    await expect(page.locator('#search-pictograms')).toBeVisible()
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

    // Category tabs with names
    const tabs = page.getByRole('tab')
    const tabCount = await tabs.count()
    expect(tabCount).toBeGreaterThanOrEqual(10)
  })
})
