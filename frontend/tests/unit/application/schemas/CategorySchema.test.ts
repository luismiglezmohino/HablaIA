import { describe, it, expect } from 'vitest'
import { CategorySchema, CategoriesArraySchema } from '@/application/schemas/CategorySchema'

describe('CategorySchema', () => {
  const validCategory = {
    id: '550e8400-e29b-41d4-a716-446655440001',
    name: 'Acciones',
    icon: 'running',
    colorHex: '#22C55E',
    displayOrder: 2,
  }

  it('parses a valid category', () => {
    const result = CategorySchema.parse(validCategory)

    expect(result).toEqual(validCategory)
  })

  it('accepts null icon', () => {
    const result = CategorySchema.parse({ ...validCategory, icon: null })

    expect(result.icon).toBeNull()
  })

  it('rejects invalid UUID', () => {
    expect(() => CategorySchema.parse({ ...validCategory, id: 'not-a-uuid' })).toThrow()
  })

  it('rejects empty name', () => {
    expect(() => CategorySchema.parse({ ...validCategory, name: '' })).toThrow()
  })

  it('rejects name longer than 50 characters', () => {
    expect(() => CategorySchema.parse({ ...validCategory, name: 'a'.repeat(51) })).toThrow()
  })

  it('accepts lowercase hex in colorHex', () => {
    const result = CategorySchema.parse({ ...validCategory, colorHex: '#22c55e' })

    expect(result.colorHex).toBe('#22c55e')
  })

  it('rejects invalid colorHex format', () => {
    expect(() => CategorySchema.parse({ ...validCategory, colorHex: 'red' })).toThrow()
    expect(() => CategorySchema.parse({ ...validCategory, colorHex: '#GGG' })).toThrow()
  })

  it('rejects negative displayOrder', () => {
    expect(() => CategorySchema.parse({ ...validCategory, displayOrder: -1 })).toThrow()
  })

  it('rejects non-integer displayOrder', () => {
    expect(() => CategorySchema.parse({ ...validCategory, displayOrder: 1.5 })).toThrow()
  })
})

describe('CategoriesArraySchema', () => {
  it('parses an array of categories', () => {
    const categories = [
      {
        id: '550e8400-e29b-41d4-a716-446655440001',
        name: 'Acciones',
        icon: 'running',
        colorHex: '#22C55E',
        displayOrder: 2,
      },
      {
        id: '550e8400-e29b-41d4-a716-446655440002',
        name: 'Emociones',
        icon: null,
        colorHex: '#3B82F6',
        displayOrder: 3,
      },
    ]

    const result = CategoriesArraySchema.parse(categories)

    expect(result).toHaveLength(2)
  })

  it('parses an empty array', () => {
    const result = CategoriesArraySchema.parse([])

    expect(result).toEqual([])
  })

  it('rejects if any category is invalid', () => {
    const categories = [
      {
        id: '550e8400-e29b-41d4-a716-446655440001',
        name: 'Acciones',
        icon: 'running',
        colorHex: '#22C55E',
        displayOrder: 2,
      },
      { id: 'invalid', name: '', icon: null, colorHex: 'bad', displayOrder: -1 },
    ]

    expect(() => CategoriesArraySchema.parse(categories)).toThrow()
  })
})
