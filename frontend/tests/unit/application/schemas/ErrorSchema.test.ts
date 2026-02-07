import { describe, it, expect } from 'vitest'
import { ErrorResponseSchema, RateLimitErrorSchema } from '@/application/schemas/ErrorSchema'

describe('ErrorResponseSchema', () => {
  it('parses a valid error response', () => {
    const result = ErrorResponseSchema.parse({ error: 'Category not found' })

    expect(result.error).toBe('Category not found')
  })

  it('rejects missing error field', () => {
    expect(() => ErrorResponseSchema.parse({})).toThrow()
  })

  it('rejects empty error message', () => {
    expect(() => ErrorResponseSchema.parse({ error: '' })).toThrow()
  })
})

describe('RateLimitErrorSchema', () => {
  it('parses a valid rate limit error', () => {
    const result = RateLimitErrorSchema.parse({
      error: 'Too many requests',
      retryAfter: 1707058200,
    })

    expect(result.error).toBe('Too many requests')
    expect(result.retryAfter).toBe(1707058200)
  })

  it('rejects missing retryAfter', () => {
    expect(() => RateLimitErrorSchema.parse({ error: 'Too many requests' })).toThrow()
  })

  it('rejects non-integer retryAfter', () => {
    expect(() =>
      RateLimitErrorSchema.parse({ error: 'Too many requests', retryAfter: 1.5 }),
    ).toThrow()
  })
})
