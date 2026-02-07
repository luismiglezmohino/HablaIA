import { describe, it, expect, vi, beforeEach } from 'vitest'
import { HttpPictogramRepository } from '@/infrastructure/http/HttpPictogramRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { PictogramsArraySchema } from '@/application/schemas/PictogramSchema'

describe('HttpPictogramRepository', () => {
  let mockClient: ApiClient
  let repository: HttpPictogramRepository

  beforeEach(() => {
    mockClient = {
      get: vi.fn(),
      post: vi.fn(),
    } as unknown as ApiClient
    repository = new HttpPictogramRepository(mockClient)
  })

  describe('findByCategory', () => {
    it('calls ApiClient.get with categoryId query param', async () => {
      const pictograms = [
        {
          id: '660e8400-e29b-41d4-a716-446655440001',
          arasaacId: 2345,
          categoryId: '550e8400-e29b-41d4-a716-446655440001',
          label: 'comer',
          imagePath: '/pictograms/2345.png',
        },
      ]
      vi.mocked(mockClient.get).mockResolvedValue(pictograms)

      const result = await repository.findByCategory('550e8400-e29b-41d4-a716-446655440001')

      expect(mockClient.get).toHaveBeenCalledWith(
        '/pictograms?categoryId=550e8400-e29b-41d4-a716-446655440001',
        PictogramsArraySchema,
      )
      expect(result).toEqual(pictograms)
    })
  })

  describe('findByCategory', () => {
    it('propagates errors from ApiClient', async () => {
      vi.mocked(mockClient.get).mockRejectedValue(new Error('Network error'))

      await expect(
        repository.findByCategory('550e8400-e29b-41d4-a716-446655440001'),
      ).rejects.toThrow('Network error')
    })
  })

  describe('search', () => {
    it('calls ApiClient.get with encoded query param', async () => {
      vi.mocked(mockClient.get).mockResolvedValue([])

      await repository.search('comer pan')

      expect(mockClient.get).toHaveBeenCalledWith(
        '/pictograms/search?q=comer%20pan',
        PictogramsArraySchema,
      )
    })

    it('returns matching pictograms', async () => {
      const pictograms = [
        {
          id: '660e8400-e29b-41d4-a716-446655440001',
          arasaacId: 2345,
          categoryId: '550e8400-e29b-41d4-a716-446655440001',
          label: 'comer',
          imagePath: '/pictograms/2345.png',
        },
      ]
      vi.mocked(mockClient.get).mockResolvedValue(pictograms)

      const result = await repository.search('comer')

      expect(result).toEqual(pictograms)
    })

    it('propagates errors from ApiClient', async () => {
      vi.mocked(mockClient.get).mockRejectedValue(new Error('Search failed'))

      await expect(repository.search('comer')).rejects.toThrow('Search failed')
    })
  })
})
