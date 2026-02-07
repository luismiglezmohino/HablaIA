import type { Pictogram } from '@/domain/entities/Pictogram'

export interface PictogramRepository {
  findByCategory(categoryId: string): Promise<Pictogram[]>
  search(query: string): Promise<Pictogram[]>
}
