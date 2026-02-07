import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import PictogramGrid from '@/presentation/components/PictogramGrid.vue'
import { usePictogramStore } from '@/application/stores/usePictogramStore'
import { useCategoryStore } from '@/application/stores/useCategoryStore'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import type { Pictogram } from '@/domain/entities/Pictogram'

const pictogramFixtures: Pictogram[] = [
  { id: 'p1', arasaacId: 2345, categoryId: 'cat-1', label: 'comer', imagePath: '/pictograms/2345.png' },
  { id: 'p2', arasaacId: 3456, categoryId: 'cat-1', label: 'beber', imagePath: '/pictograms/3456.png' },
  { id: 'p3', arasaacId: 4567, categoryId: 'cat-1', label: 'dormir', imagePath: '/pictograms/4567.png' },
]

function mountGrid(pictograms: Pictogram[] = pictogramFixtures, categoryColor = '#22C55E') {
  const pinia = createPinia()
  setActivePinia(pinia)

  const pictogramStore = usePictogramStore()
  pictogramStore.pictograms = pictograms

  const categoryStore = useCategoryStore()
  categoryStore.categories = [
    { id: 'cat-1', name: 'Acciones', icon: 'play', colorHex: categoryColor, displayOrder: 1 },
  ]
  categoryStore.selectCategory('cat-1')

  return {
    wrapper: mount(PictogramGrid, { global: { plugins: [pinia] } }),
    pictogramStore,
    categoryStore,
  }
}

describe('PictogramGrid', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('rendering', () => {
    it('renders all pictograms as cards', () => {
      const { wrapper } = mountGrid()

      const cards = wrapper.findAll('img')

      expect(cards).toHaveLength(3)
    })

    it('renders pictogram labels', () => {
      const { wrapper } = mountGrid()

      expect(wrapper.text()).toContain('comer')
      expect(wrapper.text()).toContain('beber')
      expect(wrapper.text()).toContain('dormir')
    })

    it('uses responsive grid layout', () => {
      const { wrapper } = mountGrid()

      const grid = wrapper.find('[role="grid"]')

      expect(grid.exists()).toBe(true)
      const classes = grid.classes()
      expect(classes.some((c) => c.includes('grid-cols-'))).toBe(true)
    })
  })

  describe('empty state', () => {
    it('shows empty message when no category selected', () => {
      const pinia = createPinia()
      setActivePinia(pinia)

      const wrapper = mount(PictogramGrid, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('Selecciona una categoría')
    })

    it('shows empty message when category has no pictograms', () => {
      const { wrapper } = mountGrid([])

      expect(wrapper.text()).toContain('No hay pictogramas')
    })
  })

  describe('loading state', () => {
    it('shows loading indicator when store is loading', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = usePictogramStore()
      store.loading = true
      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'cat-1', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 1 },
      ]
      categoryStore.selectCategory('cat-1')

      const wrapper = mount(PictogramGrid, { global: { plugins: [pinia] } })

      expect(wrapper.find('[role="status"]').exists()).toBe(true)
    })
  })

  describe('error state', () => {
    it('shows error message when store has error', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = usePictogramStore()
      store.error = 'Network error'
      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'cat-1', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 1 },
      ]
      categoryStore.selectCategory('cat-1')

      const wrapper = mount(PictogramGrid, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('Network error')
    })
  })

  describe('interaction', () => {
    it('emits select when a pictogram card is clicked', async () => {
      const { wrapper } = mountGrid()

      const firstButton = wrapper.findAll('button').at(0)
      await firstButton?.trigger('click')

      expect(wrapper.emitted('select')).toBeTruthy()
      expect(wrapper.emitted('select')?.at(0)).toEqual([pictogramFixtures[0]])
    })
  })

  describe('accessibility', () => {
    it('has aria-label on the grid', () => {
      const { wrapper } = mountGrid()

      const grid = wrapper.find('[role="grid"]')

      expect(grid.attributes('aria-label')).toBeTruthy()
    })

    it('disables pictogram buttons when phrase selection is full', () => {
      const pinia = createPinia()
      setActivePinia(pinia)

      const pictogramStore = usePictogramStore()
      pictogramStore.pictograms = pictogramFixtures

      const categoryStore = useCategoryStore()
      categoryStore.categories = [
        { id: 'cat-1', name: 'Acciones', icon: 'play', colorHex: '#22C55E', displayOrder: 1 },
      ]
      categoryStore.selectCategory('cat-1')

      const phraseStore = usePhraseStore()
      for (let i = 0; i < 10; i++) {
        phraseStore.addPictogram({
          id: `p${i}`,
          arasaacId: i,
          categoryId: 'cat-1',
          label: `picto-${i}`,
          imagePath: `/pictograms/${i}.png`,
        })
      }

      const wrapper = mount(PictogramGrid, { global: { plugins: [pinia] } })

      const buttons = wrapper.findAll('button')
      buttons.forEach((btn) => {
        expect(btn.attributes('disabled')).toBeDefined()
      })
    })
  })
})
