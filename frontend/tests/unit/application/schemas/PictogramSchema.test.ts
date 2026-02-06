import { describe, it, expect } from 'vitest'
import {
  PictogramSchema,
  PictogramsArraySchema,
} from '@/application/schemas/PictogramSchema'

describe('PictogramSchema', () => {
  const validPictogram = {
    id: '660e8400-e29b-41d4-a716-446655440001',
    arasaacId: 2345,
    categoryId: '550e8400-e29b-41d4-a716-446655440001',
    label: 'comer',
    imagePath: '/pictograms/2345.png',
  }

  it('parses a valid pictogram', () => {
    const result = PictogramSchema.parse(validPictogram)

    expect(result).toEqual(validPictogram)
  })

  it('rejects invalid UUID for id', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, id: 'bad' })).toThrow()
  })

  it('rejects invalid UUID for categoryId', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, categoryId: 'bad' })).toThrow()
  })

  it('rejects arasaacId less than 1', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, arasaacId: 0 })).toThrow()
  })

  it('rejects non-integer arasaacId', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, arasaacId: 1.5 })).toThrow()
  })

  it('rejects empty label', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, label: '' })).toThrow()
  })

  it('rejects label longer than 100 characters', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, label: 'a'.repeat(101) })).toThrow()
  })

  it('rejects empty imagePath', () => {
    expect(() => PictogramSchema.parse({ ...validPictogram, imagePath: '' })).toThrow()
  })

  it('rejects imagePath longer than 500 characters', () => {
    expect(() =>
      PictogramSchema.parse({ ...validPictogram, imagePath: '/'.concat('a'.repeat(501)) }),
    ).toThrow()
  })
})

describe('PictogramsArraySchema', () => {
  it('parses an array of pictograms', () => {
    const pictograms = [
      {
        id: '660e8400-e29b-41d4-a716-446655440001',
        arasaacId: 2345,
        categoryId: '550e8400-e29b-41d4-a716-446655440001',
        label: 'comer',
        imagePath: '/pictograms/2345.png',
      },
    ]

    const result = PictogramsArraySchema.parse(pictograms)

    expect(result).toHaveLength(1)
  })

  it('parses an empty array', () => {
    expect(PictogramsArraySchema.parse([])).toEqual([])
  })
})
