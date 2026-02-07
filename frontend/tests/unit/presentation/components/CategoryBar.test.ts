import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import CategoryBar from '@/presentation/components/CategoryBar.vue'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import type { Category } from '@/domain/entities/Category'

const categoryFixtures: Category[] = [
  { id: '1', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
  { id: '2', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 2 },
  { id: '3', name: 'Emociones', icon: 'heart', colorHex: '#3B82F6', displayOrder: 3 },
]

function mountCategoryBar(categories: Category[] = categoryFixtures) {
  const pinia = createPinia()
  setActivePinia(pinia)

  const store = useCategoryStore()
  store.categories = categories

  return {
    wrapper: mount(CategoryBar, { global: { plugins: [pinia] } }),
    store,
  }
}

describe('CategoryBar', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('rendering', () => {
    it('renders all categories sorted by displayOrder', () => {
      const { wrapper } = mountCategoryBar()

      const buttons = wrapper.findAll('[role="tab"]')

      expect(buttons).toHaveLength(3)
      expect(buttons.at(0)?.text()).toContain('Personas')
      expect(buttons.at(1)?.text()).toContain('Acciones')
      expect(buttons.at(2)?.text()).toContain('Emociones')
    })

    it('renders nothing when categories are empty', () => {
      const { wrapper } = mountCategoryBar([])

      expect(wrapper.findAll('[role="tab"]')).toHaveLength(0)
    })

    it('uses role="tablist" for the container', () => {
      const { wrapper } = mountCategoryBar()

      expect(wrapper.find('[role="tablist"]').exists()).toBe(true)
    })

    it('applies category color as border style', () => {
      const { wrapper } = mountCategoryBar()

      const firstTab = wrapper.findAll('[role="tab"]').at(0)

      expect(firstTab?.attributes('style')).toContain('border-color: #FBBF24')
    })

    it('renders Lucide icon for categories with known icon', () => {
      const { wrapper } = mountCategoryBar()

      const firstTab = wrapper.findAll('[role="tab"]').at(0)

      expect(firstTab?.find('svg').exists()).toBe(true)
    })

    it('renders fallback icon for unknown icon names', () => {
      const { wrapper } = mountCategoryBar([
        { id: '1', name: 'Nueva', icon: 'unknown-icon', colorHex: '#FF0000', displayOrder: 1 },
      ])

      const tab = wrapper.find('[role="tab"]')

      expect(tab.find('svg').exists()).toBe(true)
    })

    it('renders fallback icon when icon is null', () => {
      const { wrapper } = mountCategoryBar([
        { id: '1', name: 'Nueva', icon: null, colorHex: '#FF0000', displayOrder: 1 },
      ])

      const tab = wrapper.find('[role="tab"]')

      expect(tab.find('svg').exists()).toBe(true)
    })

    it('hides icon from screen readers', () => {
      const { wrapper } = mountCategoryBar()

      const svg = wrapper.find('[role="tab"] svg')

      expect(svg.attributes('aria-hidden')).toBe('true')
    })
  })

  describe('accessibility', () => {
    it('has aria-label on each tab', () => {
      const { wrapper } = mountCategoryBar()

      const tabs = wrapper.findAll('[role="tab"]')

      tabs.forEach((tab) => {
        expect(tab.attributes('aria-label')).toBeTruthy()
      })
    })

    it('marks selected tab with aria-selected="true"', async () => {
      const { wrapper, store } = mountCategoryBar()

      store.selectCategory('2')
      await wrapper.vm.$nextTick()

      const tabs = wrapper.findAll('[role="tab"]')
      expect(tabs.at(1)?.attributes('aria-selected')).toBe('true')
      expect(tabs.at(0)?.attributes('aria-selected')).toBe('false')
    })

    it('has minimum touch target size (44x44)', () => {
      const { wrapper } = mountCategoryBar()

      const tab = wrapper.find('[role="tab"]')
      const classes = tab.classes()

      expect(classes.some((c) => c.includes('min-h-') || c === 'min-h-touch')).toBe(true)
      expect(classes.some((c) => c.includes('min-w-') || c === 'min-w-touch')).toBe(true)
    })
  })

  describe('interaction', () => {
    it('selects category on click', async () => {
      const { wrapper, store } = mountCategoryBar()

      await wrapper.findAll('[role="tab"]').at(1)?.trigger('click')

      expect(store.selectedCategoryId).toBe('2')
    })

    it('navigates with arrow keys', async () => {
      const { wrapper, store } = mountCategoryBar()

      const tabs = wrapper.findAll('[role="tab"]')
      await tabs.at(0)?.trigger('click')
      expect(store.selectedCategoryId).toBe('1')

      await tabs.at(0)?.trigger('keydown', { key: 'ArrowRight' })

      expect(store.selectedCategoryId).toBe('2')
    })

    it('wraps around with ArrowRight on last tab', async () => {
      const { wrapper, store } = mountCategoryBar()

      const tabs = wrapper.findAll('[role="tab"]')
      await tabs.at(2)?.trigger('click')
      expect(store.selectedCategoryId).toBe('3')

      await tabs.at(2)?.trigger('keydown', { key: 'ArrowRight' })

      expect(store.selectedCategoryId).toBe('1')
    })

    it('wraps around with ArrowLeft on first tab', async () => {
      const { wrapper, store } = mountCategoryBar()

      const tabs = wrapper.findAll('[role="tab"]')
      await tabs.at(0)?.trigger('click')
      expect(store.selectedCategoryId).toBe('1')

      await tabs.at(0)?.trigger('keydown', { key: 'ArrowLeft' })

      expect(store.selectedCategoryId).toBe('3')
    })
  })

  describe('loading state', () => {
    it('shows loading indicator when store is loading', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = useCategoryStore()
      store.loading = true

      const wrapper = mount(CategoryBar, { global: { plugins: [pinia] } })

      expect(wrapper.find('[role="status"]').exists()).toBe(true)
    })

    it('shows error message when store has error', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = useCategoryStore()
      store.error = 'Network error'

      const wrapper = mount(CategoryBar, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('Network error')
    })
  })
})
