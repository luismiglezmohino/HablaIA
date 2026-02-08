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

  it('has accessible label', () => {
    const wrapper = mount(SearchBar)

    expect(wrapper.find('label[for="search-pictograms"]').exists()).toBe(true)
    expect(wrapper.find('#search-pictograms').exists()).toBe(true)
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
})
