import { describe, it, expect } from 'vitest'
import { PhraseResponseSchema } from '@/application/schemas/PhraseResponseSchema'

describe('PhraseResponseSchema', () => {
  const validResponse = {
    variations: ['Quiero comer pan', 'Me gustaría comer pan', 'Deseo comer pan'],
    source: 'generated',
    sequenceHash: 'a'.repeat(64),
    pictogramIds: [
      '660e8400-e29b-41d4-a716-446655440001',
      '660e8400-e29b-41d4-a716-446655440002',
    ],
  }

  it('parses a valid phrase response', () => {
    const result = PhraseResponseSchema.parse(validResponse)

    expect(result).toEqual(validResponse)
  })

  it('accepts source "cache"', () => {
    const result = PhraseResponseSchema.parse({ ...validResponse, source: 'cache' })

    expect(result.source).toBe('cache')
  })

  it('accepts source "fallback"', () => {
    const result = PhraseResponseSchema.parse({ ...validResponse, source: 'fallback' })

    expect(result.source).toBe('fallback')
  })

  it('accepts a single variation (fallback case)', () => {
    const result = PhraseResponseSchema.parse({
      ...validResponse,
      variations: ['comer pan'],
    })

    expect(result.variations).toHaveLength(1)
  })

  it('rejects empty variations array', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, variations: [] }),
    ).toThrow()
  })

  it('rejects more than 3 variations', () => {
    expect(() =>
      PhraseResponseSchema.parse({
        ...validResponse,
        variations: ['a', 'b', 'c', 'd'],
      }),
    ).toThrow()
  })

  it('rejects invalid source value', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, source: 'unknown' }),
    ).toThrow()
  })

  it('rejects empty pictogramIds array', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, pictogramIds: [] }),
    ).toThrow()
  })

  it('rejects more than 10 pictogramIds', () => {
    const ids = Array.from({ length: 11 }, (_, i) =>
      `660e8400-e29b-41d4-a716-44665544${String(i).padStart(4, '0')}`,
    )
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, pictogramIds: ids }),
    ).toThrow()
  })

  it('rejects invalid UUID in pictogramIds', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, pictogramIds: ['bad-uuid'] }),
    ).toThrow()
  })

  it('rejects variation longer than 500 characters', () => {
    expect(() =>
      PhraseResponseSchema.parse({
        ...validResponse,
        variations: ['a'.repeat(501)],
      }),
    ).toThrow()
  })

  it('rejects empty sequenceHash', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, sequenceHash: '' }),
    ).toThrow()
  })

  it('rejects sequenceHash longer than 128 characters', () => {
    expect(() =>
      PhraseResponseSchema.parse({ ...validResponse, sequenceHash: 'a'.repeat(129) }),
    ).toThrow()
  })
})
