import { describe, it, expect, vi, beforeEach } from 'vitest'
import { HttpCategoryRepository } from '@/infrastructure/http/HttpCategoryRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { CategoriesArraySchema } from '@/application/schemas/CategorySchema'

describe('HttpCategoryRepository', () => {
  let mockClient: ApiClient
  let repository: HttpCategoryRepository

  beforeEach(() => {
    mockClient = {
      get: vi.fn(),
      post: vi.fn(),
    } as unknown as ApiClient
    repository = new HttpCategoryRepository(mockClient)
  })

  describe('findAll', () => {
    it('calls ApiClient.get with correct path and schema', async () => {
      const categories = [
        {
          id: '550e8400-e29b-41d4-a716-446655440001',
          name: 'Acciones',
          icon: 'running',
          colorHex: '#22C55E',
          displayOrder: 2,
        },
      ]
      vi.mocked(mockClient.get).mockResolvedValue(categories)

      const result = await repository.findAll()

      expect(mockClient.get).toHaveBeenCalledWith('/categories', CategoriesArraySchema)
      expect(result).toEqual(categories)
    })

    it('propagates errors from ApiClient', async () => {
      vi.mocked(mockClient.get).mockRejectedValue(new Error('Network error'))

      await expect(repository.findAll()).rejects.toThrow('Network error')
    })
  })
})
