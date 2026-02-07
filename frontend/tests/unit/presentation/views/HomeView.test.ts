import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import HomeView from '@/presentation/views/HomeView.vue'
import { useCategoryStore } from '@/application/stores/useCategoryStore'

beforeEach(() => {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([]),
    }),
  )
})

describe('HomeView', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders the app title', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(wrapper.text()).toContain('HablaIA')
  })

  it('renders the CategoryBar component', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(wrapper.findComponent({ name: 'CategoryBar' }).exists()).toBe(true)
  })

  it('has a main content area', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(wrapper.find('main').exists()).toBe(true)
  })

  it('has a skip link for keyboard navigation', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    const skipLink = wrapper.find('.skip-link')

    expect(skipLink.exists()).toBe(true)
    expect(skipLink.attributes('href')).toBe('#main-content')
  })

  it('has an accessible heading structure', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    const h1 = wrapper.find('h1')

    expect(h1.exists()).toBe(true)
    expect(h1.text()).toContain('HablaIA')
  })

  it('has an aria-live region for status feedback', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    const liveRegion = wrapper.find('[role="status"][aria-live="polite"]')

    expect(liveRegion.exists()).toBe(true)
  })

  it('announces selected category via aria-live', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const store = useCategoryStore()
    store.categories = [
      { id: '1', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
    ]

    const wrapper = mount(HomeView, { global: { plugins: [pinia] } })

    store.selectCategory('1')
    await wrapper.vm.$nextTick()

    const liveRegion = wrapper.find('[role="status"][aria-live="polite"]')
    expect(liveRegion.text()).toContain('Categoría Personas seleccionada')
  })

  it('renders the PictogramGrid component', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(wrapper.findComponent({ name: 'PictogramGrid' }).exists()).toBe(true)
  })

  it('calls fetchCategories on mount', () => {
    const fetchSpy = vi.mocked(fetch)

    mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(fetchSpy).toHaveBeenCalledWith(
      expect.stringContaining('/categories'),
      expect.objectContaining({ method: 'GET' }),
    )
  })
})
