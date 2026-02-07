import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import PhraseBar from '@/presentation/components/PhraseBar.vue'
import { usePhraseStore } from '@/application/stores/usePhraseStore'
import type { Pictogram } from '@/domain/entities/Pictogram'

const pictogramFixtures: Pictogram[] = [
  { id: 'p1', arasaacId: 2345, categoryId: 'cat-1', label: 'comer', imagePath: '/pictograms/2345.png' },
  { id: 'p2', arasaacId: 3456, categoryId: 'cat-1', label: 'pan', imagePath: '/pictograms/3456.png' },
]

function mountPhraseBar(pictograms: Pictogram[] = []) {
  const pinia = createPinia()
  setActivePinia(pinia)

  const store = usePhraseStore()
  pictograms.forEach((p) => store.addPictogram(p))

  return {
    wrapper: mount(PhraseBar, { global: { plugins: [pinia] } }),
    store,
  }
}

describe('PhraseBar', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('rendering', () => {
    it('shows empty state when no pictograms selected', () => {
      const { wrapper } = mountPhraseBar()

      expect(wrapper.text()).toContain('Selecciona pictogramas')
    })

    it('shows selected pictograms as chips', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      expect(wrapper.text()).toContain('comer')
      expect(wrapper.text()).toContain('pan')
    })

    it('shows pictogram thumbnails in chips', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      const images = wrapper.findAll('img')

      expect(images).toHaveLength(2)
      expect(images.at(0)?.attributes('src')).toBe('/pictograms/2345.png')
    })

    it('shows generate button', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      const button = wrapper.find('[data-testid="generate-btn"]')

      expect(button.exists()).toBe(true)
    })

    it('shows clear button when pictograms selected', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      const button = wrapper.find('[data-testid="clear-btn"]')

      expect(button.exists()).toBe(true)
    })

    it('hides clear button when no pictograms selected', () => {
      const { wrapper } = mountPhraseBar()

      const button = wrapper.find('[data-testid="clear-btn"]')

      expect(button.exists()).toBe(false)
    })

    it('shows pictogram count', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      expect(wrapper.text()).toContain('2')
    })
  })

  describe('interaction', () => {
    it('removes pictogram when chip remove button is clicked', async () => {
      const { wrapper, store } = mountPhraseBar(pictogramFixtures)

      const removeButtons = wrapper.findAll('[data-testid="remove-chip"]')
      await removeButtons.at(0)?.trigger('click')

      expect(store.selectedPictograms).toHaveLength(1)
      expect(store.selectedPictograms[0]?.label).toBe('pan')
    })

    it('clears all pictograms on clear button click', async () => {
      const { wrapper, store } = mountPhraseBar(pictogramFixtures)

      await wrapper.find('[data-testid="clear-btn"]').trigger('click')

      expect(store.selectedPictograms).toHaveLength(0)
    })

    it('does not show generate button when no pictograms', () => {
      const { wrapper } = mountPhraseBar()

      const button = wrapper.find('[data-testid="generate-btn"]')

      expect(button.exists()).toBe(false)
    })

    it('enables generate button when pictograms selected', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      const button = wrapper.find('[data-testid="generate-btn"]')

      expect(button.attributes('disabled')).toBeUndefined()
    })
  })

  describe('accessibility', () => {
    it('has aria-label on the region', () => {
      const { wrapper } = mountPhraseBar()

      expect(wrapper.find('[aria-label]').exists()).toBe(true)
    })

    it('has aria-label on remove buttons', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      const removeButtons = wrapper.findAll('[data-testid="remove-chip"]')

      removeButtons.forEach((btn) => {
        expect(btn.attributes('aria-label')).toBeTruthy()
      })
    })

    it('has aria-live for selection changes', () => {
      const { wrapper } = mountPhraseBar(pictogramFixtures)

      expect(wrapper.find('[aria-live]').exists()).toBe(true)
    })
  })

  describe('phrase results', () => {
    it('shows phrase variations when response exists', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.phraseResponse = {
        variations: ['Quiero comer pan', 'Me gustaría comer pan'],
        source: 'generated',
        sequenceHash: 'abc',
        pictogramIds: ['p1', 'p2'],
      }

      const wrapper = mount(PhraseBar, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('Quiero comer pan')
      expect(wrapper.text()).toContain('Me gustaría comer pan')
    })

    it('shows source badge for phrase response', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.phraseResponse = {
        variations: ['Quiero comer pan'],
        source: 'cache',
        sequenceHash: 'abc',
        pictogramIds: ['p1'],
      }

      const wrapper = mount(PhraseBar, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('cache')
    })

    it('shows error message when generation fails', () => {
      const pinia = createPinia()
      setActivePinia(pinia)
      const store = usePhraseStore()
      store.addPictogram(pictogramFixtures[0]!)
      store.error = 'Too many requests'

      const wrapper = mount(PhraseBar, { global: { plugins: [pinia] } })

      expect(wrapper.text()).toContain('Too many requests')
    })
  })
})
