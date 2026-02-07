import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PictogramCard from '@/presentation/components/PictogramCard.vue'
import type { Pictogram } from '@/domain/entities/Pictogram'

const pictogramFixture: Pictogram = {
  id: 'p1',
  arasaacId: 2345,
  categoryId: 'cat-1',
  label: 'comer',
  imagePath: '/pictograms/2345.png',
}

function mountCard(props: { pictogram: Pictogram; categoryColor?: string }) {
  return mount(PictogramCard, {
    props: {
      categoryColor: '#22C55E',
      ...props,
    },
  })
}

describe('PictogramCard', () => {
  describe('rendering', () => {
    it('renders the pictogram label', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      expect(wrapper.text()).toContain('comer')
    })

    it('renders the ARASAAC image with correct src', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      const img = wrapper.find('img')

      expect(img.exists()).toBe(true)
      expect(img.attributes('src')).toBe('/pictograms/2345.png')
    })

    it('uses label as image alt text', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      const img = wrapper.find('img')

      expect(img.attributes('alt')).toBe('comer')
    })

    it('applies category color as border-top', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture, categoryColor: '#FBBF24' })

      expect(wrapper.attributes('style')).toContain('border-top-color: #FBBF24')
    })

    it('renders as a button for selection', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      expect(wrapper.find('button').exists()).toBe(true)
    })
  })

  describe('accessibility', () => {
    it('has aria-label with pictogram label', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      const button = wrapper.find('button')

      expect(button.attributes('aria-label')).toContain('comer')
    })

    it('has minimum touch target size (44x44)', () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      const button = wrapper.find('button')
      const classes = button.classes()

      expect(classes.some((c) => c.includes('min-h-') || c === 'min-h-touch')).toBe(true)
      expect(classes.some((c) => c.includes('min-w-') || c === 'min-w-touch')).toBe(true)
    })
  })

  describe('interaction', () => {
    it('emits select event with pictogram on click', async () => {
      const wrapper = mountCard({ pictogram: pictogramFixture })

      await wrapper.find('button').trigger('click')

      expect(wrapper.emitted('select')).toBeTruthy()
      expect(wrapper.emitted('select')?.at(0)).toEqual([pictogramFixture])
    })
  })
})
