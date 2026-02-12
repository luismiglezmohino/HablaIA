import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import SearchBar from '@/presentation/components/SearchBar.vue'

describe('SearchBar', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders a search input', () => {
    const wrapper = mount(SearchBar)

    expect(wrapper.find('input[type="search"]').exists()).toBe(true)
  })

  it('has accessible label via placeholder', () => {
    const wrapper = mount(SearchBar)

    const input = wrapper.find('#search-pictograms')
    expect(input.exists()).toBe(true)
    expect(input.attributes('placeholder')).toBe('Buscar pictogramas...')
  })

  it('emits search event on input with debounce', async () => {
    vi.useFakeTimers()
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('agua')
    vi.advanceTimersByTime(300)

    expect(wrapper.emitted('search')).toBeDefined()
    expect(wrapper.emitted('search')![0]).toEqual(['agua'])

    vi.useRealTimers()
  })

  it('does not emit search before debounce delay', async () => {
    vi.useFakeTimers()
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('ag')
    vi.advanceTimersByTime(100)

    expect(wrapper.emitted('search')).toBeUndefined()

    vi.useRealTimers()
  })

  it('emits search with empty string when input is cleared', async () => {
    vi.useFakeTimers()
    const wrapper = mount(SearchBar)

    await wrapper.find('input').setValue('agua')
    vi.advanceTimersByTime(300)

    await wrapper.find('input').setValue('')
    vi.advanceTimersByTime(300)

    const events = wrapper.emitted('search')!
    expect(events.at(-1)).toEqual([''])

    vi.useRealTimers()
  })

  it('has minimum touch target size for SAAC', () => {
    const wrapper = mount(SearchBar)
    const input = wrapper.find('input')

    expect(input.classes()).toContain('min-h-touch')
  })

  describe('escape key', () => {
    it('clears query and emits empty search immediately on Escape', async () => {
      vi.useFakeTimers()
      const wrapper = mount(SearchBar)
      const input = wrapper.find('input')

      await input.setValue('agua')
      await input.trigger('keydown', { key: 'Escape' })

      expect((input.element as HTMLInputElement).value).toBe('')
      expect(wrapper.emitted('search')?.at(-1)).toEqual([''])

      vi.useRealTimers()
    })

    it('blurs input on Escape when query is already empty', async () => {
      const wrapper = mount(SearchBar, { attachTo: document.body })
      const input = wrapper.find('input')

      ;(input.element as HTMLInputElement).focus()
      expect(document.activeElement).toBe(input.element)

      await input.trigger('keydown', { key: 'Escape' })

      expect(document.activeElement).not.toBe(input.element)
      wrapper.unmount()
    })
  })
})
