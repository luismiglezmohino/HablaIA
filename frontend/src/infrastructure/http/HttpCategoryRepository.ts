import type { Category } from '@/domain/entities/Category'
import type { CategoryRepository } from '@/domain/repositories/CategoryRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { CategoriesArraySchema } from '@/application/schemas/CategorySchema'

export class HttpCategoryRepository implements CategoryRepository {
  constructor(private readonly client: ApiClient) {}

  async findAll(): Promise<Category[]> {
    return this.client.get('/categories', CategoriesArraySchema)
  }
}
