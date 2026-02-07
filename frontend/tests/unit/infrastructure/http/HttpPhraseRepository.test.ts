import { describe, it, expect, vi, beforeEach } from 'vitest'
import { HttpPhraseRepository } from '@/infrastructure/http/HttpPhraseRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { PhraseResponseSchema } from '@/application/schemas/PhraseResponseSchema'

describe('HttpPhraseRepository', () => {
  let mockClient: ApiClient
  let repository: HttpPhraseRepository

  beforeEach(() => {
    mockClient = {
      get: vi.fn(),
      post: vi.fn(),
    } as unknown as ApiClient
    repository = new HttpPhraseRepository(mockClient)
  })

  describe('generate', () => {
    it('calls ApiClient.post with pictogramIds and correct schema', async () => {
      const response = {
        variations: ['Quiero comer pan', 'Me gustaría comer pan'],
        source: 'generated' as const,
        sequenceHash: 'a'.repeat(64),
        pictogramIds: ['660e8400-e29b-41d4-a716-446655440001'],
      }
      vi.mocked(mockClient.post).mockResolvedValue(response)

      const ids = ['660e8400-e29b-41d4-a716-446655440001']
      const result = await repository.generate(ids)

      expect(mockClient.post).toHaveBeenCalledWith(
        '/phrases/generate',
        { pictogramIds: ids },
        PhraseResponseSchema,
      )
      expect(result).toEqual(response)
    })

    it('propagates errors from ApiClient', async () => {
      vi.mocked(mockClient.post).mockRejectedValue(new Error('Rate limited'))

      await expect(repository.generate(['id1'])).rejects.toThrow('Rate limited')
    })
  })
})
