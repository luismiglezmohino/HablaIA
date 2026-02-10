import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import HomeView from '@/presentation/views/HomeView.vue'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'

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

  it('shows keyboard shortcuts footer', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    const footer = wrapper.find('footer[aria-label="Atajos de teclado"]')

    expect(footer.exists()).toBe(true)
    expect(footer.text()).toContain('Categorías')
    expect(footer.text()).toContain('Buscar')
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

  it('renders the PhraseBar component', () => {
    const wrapper = mount(HomeView, {
      global: { plugins: [createPinia()] },
    })

    expect(wrapper.findComponent({ name: 'PhraseBar' }).exists()).toBe(true)
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

  describe('screen reader announcements', () => {
    it('has an assertive live region for action announcements', () => {
      const wrapper = mount(HomeView, {
        global: { plugins: [createPinia()] },
      })

      const assertiveRegion = wrapper.find('[role="status"][aria-live="assertive"]')

      expect(assertiveRegion.exists()).toBe(true)
    })

    it('announces search status via polite live region when searching', async () => {
      vi.useFakeTimers()
      const pinia = createPinia()
      setActivePinia(pinia)

      const wrapper = mount(HomeView, { global: { plugins: [pinia] } })

      const mobileInput = wrapper.find('#mobile-search')
      await mobileInput.setValue('xyz')
      await vi.advanceTimersByTimeAsync(300)
      await wrapper.vm.$nextTick()
      await wrapper.vm.$nextTick()

      const politeRegion = wrapper.find('[role="status"][aria-live="polite"]')
      // When searching with no results, announces "Sin resultados"
      expect(politeRegion.text()).toContain('Sin resultados de búsqueda')

      vi.useRealTimers()
    })

    it('announces phrase generation result via assertive live region', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const phraseStore = usePhraseStore()
      phraseStore.addPictogram({
        id: 'p1',
        arasaacId: 2345,
        categoryId: 'cat-1',
        label: 'comer',
        imagePath: '/pictograms/2345.png',
      })

      const wrapper = mount(HomeView, { global: { plugins: [pinia] } })

      // Set phraseResponse AFTER mount so the watcher fires
      phraseStore.phraseResponse = {
        variations: ['Quiero comer', 'Me gustaría comer'],
        source: 'generated',
        sequenceHash: 'abc',
        pictogramIds: ['p1'],
      }
      await wrapper.vm.$nextTick()

      const assertiveRegion = wrapper.find('[role="status"][aria-live="assertive"]')
      expect(assertiveRegion.text()).toContain('Frase generada con 2 variaciones')
    })

    it('announces pictogram removal via assertive live region', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const phraseStore = usePhraseStore()
      phraseStore.addPictogram({
        id: 'p1',
        arasaacId: 2345,
        categoryId: 'cat-1',
        label: 'comer',
        imagePath: '/pictograms/2345.png',
      })
      phraseStore.addPictogram({
        id: 'p2',
        arasaacId: 3456,
        categoryId: 'cat-1',
        label: 'pan',
        imagePath: '/pictograms/3456.png',
      })

      const wrapper = mount(HomeView, { global: { plugins: [pinia] } })

      phraseStore.removePictogram(0)
      await wrapper.vm.$nextTick()

      const assertiveRegion = wrapper.find('[role="status"][aria-live="assertive"]')
      expect(assertiveRegion.text()).toContain('Pictograma eliminado de la frase')
    })

    it('mobile phrase results list is focusable for screen readers', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const phraseStore = usePhraseStore()
      phraseStore.addPictogram({
        id: 'p1',
        arasaacId: 2345,
        categoryId: 'cat-1',
        label: 'comer',
        imagePath: '/pictograms/2345.png',
      })
      phraseStore.phraseResponse = {
        variations: ['Quiero comer'],
        source: 'generated',
        sequenceHash: 'abc',
        pictogramIds: ['p1'],
      }

      const wrapper = mount(HomeView, { global: { plugins: [pinia] } })
      const mobileResults = wrapper.find('section[aria-label="Frases generadas"] ul[role="list"]')

      expect(mobileResults.attributes('tabindex')).toBe('-1')
    })
  })

  describe('keyboard shortcuts', () => {
    it('focuses search input when "/" key is pressed', async () => {
      const wrapper = mount(HomeView, {
        global: { plugins: [createPinia()] },
        attachTo: document.body,
      })

      await wrapper.trigger('keydown', { key: '/' })
      await wrapper.vm.$nextTick()

      const searchInput = wrapper.find('#search-pictograms')
      expect(document.activeElement).toBe(searchInput.element)
      wrapper.unmount()
    })

    it('does not focus search when "/" is pressed inside an input', async () => {
      const wrapper = mount(HomeView, {
        global: { plugins: [createPinia()] },
        attachTo: document.body,
      })

      const mobileInput = wrapper.find('#mobile-search')
      ;(mobileInput.element as HTMLInputElement).focus()

      // Dispatch from the input element so event.target is an HTMLInputElement
      const event = new KeyboardEvent('keydown', { key: '/', bubbles: true })
      mobileInput.element.dispatchEvent(event)
      await wrapper.vm.$nextTick()

      // Focus should stay on mobile input, not move to desktop search
      expect(document.activeElement).toBe(mobileInput.element)
      wrapper.unmount()
    })

    it('selects category when number key 1 is pressed', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'c1', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
        { id: 'c2', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 2 },
      ]

      mount(HomeView, {
        global: { plugins: [pinia] },
        attachTo: document.body,
      })

      const event = new KeyboardEvent('keydown', { key: '1', bubbles: true })
      document.dispatchEvent(event)

      expect(categoryStore.selectedCategoryId).toBe('c1')
    })

    it('selects category when number key 2 is pressed', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'c1', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
        { id: 'c2', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 2 },
      ]

      mount(HomeView, {
        global: { plugins: [pinia] },
        attachTo: document.body,
      })

      const event = new KeyboardEvent('keydown', { key: '2', bubbles: true })
      document.dispatchEvent(event)

      expect(categoryStore.selectedCategoryId).toBe('c2')
    })

    it('selects category 10 when "0" key is pressed', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const categoryStore = useCategoryStore()
      const cats = Array.from({ length: 10 }, (_, i) => ({
        id: `c${i + 1}`,
        name: `Cat ${i + 1}`,
        icon: 'users',
        colorHex: '#FBBF24',
        displayOrder: i + 1,
      }))
      categoryStore.categories = cats

      mount(HomeView, {
        global: { plugins: [pinia] },
        attachTo: document.body,
      })

      const event = new KeyboardEvent('keydown', { key: '0', bubbles: true })
      document.dispatchEvent(event)

      expect(categoryStore.selectedCategoryId).toBe('c10')
    })

    it('selects category 11 when "?" key is pressed', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const categoryStore = useCategoryStore()
      const cats = Array.from({ length: 11 }, (_, i) => ({
        id: `c${i + 1}`,
        name: `Cat ${i + 1}`,
        icon: 'users',
        colorHex: '#FBBF24',
        displayOrder: i + 1,
      }))
      categoryStore.categories = cats

      mount(HomeView, {
        global: { plugins: [pinia] },
        attachTo: document.body,
      })

      const event = new KeyboardEvent('keydown', { key: '?', bubbles: true })
      document.dispatchEvent(event)

      expect(categoryStore.selectedCategoryId).toBe('c11')
    })

    it('ignores number keys when focus is inside an input', async () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'c1', name: 'Personas', icon: 'users', colorHex: '#FBBF24', displayOrder: 1 },
      ]

      const wrapper = mount(HomeView, {
        global: { plugins: [pinia] },
        attachTo: document.body,
      })

      const mobileInput = wrapper.find('#mobile-search')
      ;(mobileInput.element as HTMLInputElement).focus()

      const event = new KeyboardEvent('keydown', { key: '1', bubbles: true })
      mobileInput.element.dispatchEvent(event)

      expect(categoryStore.selectedCategoryId).toBeNull()
      wrapper.unmount()
    })
  })
})
