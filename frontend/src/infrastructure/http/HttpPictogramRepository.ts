import type { Pictogram } from '@/domain/entities/Pictogram'
import type { PictogramRepository } from '@/domain/repositories/PictogramRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { PictogramsArraySchema } from '@/application/schemas/PictogramSchema'

export class HttpPictogramRepository implements PictogramRepository {
  constructor(private readonly client: ApiClient) {}

  async findByCategory(categoryId: string): Promise<Pictogram[]> {
    return this.client.get(
      `/pictograms?categoryId=${encodeURIComponent(categoryId)}`,
      PictogramsArraySchema,
    )
  }

  async search(query: string): Promise<Pictogram[]> {
    return this.client.get(
      `/pictograms/search?q=${encodeURIComponent(query)}`,
      PictogramsArraySchema,
    )
  }
}
