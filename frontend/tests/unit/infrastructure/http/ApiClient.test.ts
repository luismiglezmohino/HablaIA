import { describe, it, expect, vi, beforeEach } from 'vitest'
import { z } from 'zod'
import { ApiClient, ApiError } from '@/infrastructure/http/ApiClient'

const TestSchema = z.object({ id: z.number(), name: z.string() })

describe('ApiClient', () => {
  let client: ApiClient

  beforeEach(() => {
    client = new ApiClient('/api')
    vi.restoreAllMocks()
  })

  describe('get', () => {
    it('fetches and validates response with schema', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ id: 1, name: 'Test' }), { status: 200 }),
      )

      const result = await client.get('/test', TestSchema)

      expect(result).toEqual({ id: 1, name: 'Test' })
      expect(fetch).toHaveBeenCalledWith('/api/test', expect.objectContaining({ method: 'GET' }))
    })

    it('throws ApiError on non-ok response', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ error: 'Not found' }), { status: 404 }),
      )

      await expect(client.get('/test', TestSchema)).rejects.toThrow(ApiError)
    })

    it('includes status code in ApiError', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ error: 'Not found' }), { status: 404 }),
      )

      try {
        await client.get('/test', TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).status).toBe(404)
      }
    })

    it('throws on Zod validation failure', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ bad: 'data' }), { status: 200 }),
      )

      await expect(client.get('/test', TestSchema)).rejects.toThrow()
    })
  })

  describe('post', () => {
    it('sends POST with JSON body and validates response', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ id: 1, name: 'Created' }), { status: 200 }),
      )

      const result = await client.post('/test', { data: 'value' }, TestSchema)

      expect(result).toEqual({ id: 1, name: 'Created' })
      expect(fetch).toHaveBeenCalledWith(
        '/api/test',
        expect.objectContaining({
          method: 'POST',
          headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
          body: JSON.stringify({ data: 'value' }),
        }),
      )
    })

    it('throws ApiError with retryAfter on 429', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(
          JSON.stringify({ error: 'Too many requests', retryAfter: 1707058200 }),
          { status: 429, headers: { 'Retry-After': '1707058200' } },
        ),
      )

      try {
        await client.post('/test', {}, TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).status).toBe(429)
        expect((e as ApiError).body.retryAfter).toBe(1707058200)
      }
    })

    it('throws ApiError on 400 response', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ error: 'Invalid JSON body' }), { status: 400 }),
      )

      try {
        await client.post('/test', {}, TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).status).toBe(400)
        expect((e as ApiError).message).toBe('Invalid JSON body')
      }
    })
  })

  describe('retry on network error', () => {
    it('retries once on TypeError and succeeds', async () => {
      const fetchSpy = vi.spyOn(globalThis, 'fetch')
        .mockRejectedValueOnce(new TypeError('Failed to fetch'))
        .mockResolvedValueOnce(
          new Response(JSON.stringify({ id: 1, name: 'Retry OK' }), { status: 200 }),
        )

      const result = await client.get('/test', TestSchema)

      expect(result).toEqual({ id: 1, name: 'Retry OK' })
      expect(fetchSpy).toHaveBeenCalledTimes(2)
    })

    it('retries once on TypeError for POST and succeeds', async () => {
      const fetchSpy = vi.spyOn(globalThis, 'fetch')
        .mockRejectedValueOnce(new TypeError('Failed to fetch'))
        .mockResolvedValueOnce(
          new Response(JSON.stringify({ id: 1, name: 'Retry OK' }), { status: 200 }),
        )

      const result = await client.post('/test', { data: 'value' }, TestSchema)

      expect(result).toEqual({ id: 1, name: 'Retry OK' })
      expect(fetchSpy).toHaveBeenCalledTimes(2)
    })

    it('throws after retry also fails with TypeError', async () => {
      vi.spyOn(globalThis, 'fetch').mockRejectedValue(new TypeError('Failed to fetch'))

      await expect(client.get('/test', TestSchema)).rejects.toThrow('Failed to fetch')
    })

    it('does not retry on non-TypeError errors', async () => {
      const fetchSpy = vi.spyOn(globalThis, 'fetch')
        .mockRejectedValueOnce(new Error('Some other error'))

      await expect(client.get('/test', TestSchema)).rejects.toThrow('Some other error')
      expect(fetchSpy).toHaveBeenCalledTimes(1)
    })
  })

  describe('error handling', () => {
    it('propagates network errors from fetch after retry', async () => {
      vi.spyOn(globalThis, 'fetch').mockRejectedValue(new TypeError('Failed to fetch'))

      await expect(client.get('/test', TestSchema)).rejects.toThrow('Failed to fetch')
    })

    it('sets ApiError message from error body', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ error: 'Category not found' }), { status: 404 }),
      )

      try {
        await client.get('/test', TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).message).toBe('Category not found')
      }
    })

    it('handles non-JSON error response gracefully', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response('Internal Server Error', { status: 500 }),
      )

      try {
        await client.get('/test', TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).status).toBe(500)
        expect((e as ApiError).message).toBe('Unknown server error')
      }
    })

    it('handles malformed error body without error field', async () => {
      vi.spyOn(globalThis, 'fetch').mockResolvedValue(
        new Response(JSON.stringify({ message: 'unexpected format' }), { status: 500 }),
      )

      try {
        await client.get('/test', TestSchema)
        expect.fail('Should have thrown')
      } catch (e) {
        expect(e).toBeInstanceOf(ApiError)
        expect((e as ApiError).message).toBe('Unknown server error')
      }
    })
  })
})
