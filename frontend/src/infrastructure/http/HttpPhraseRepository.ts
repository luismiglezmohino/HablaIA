import type { PhraseResponse } from '@/domain/entities/PhraseResponse'
import type { PhraseRepository } from '@/domain/repositories/PhraseRepository'
import type { ApiClient } from '@/infrastructure/http/ApiClient'
import { PhraseResponseSchema } from '@/application/schemas/PhraseResponseSchema'

export class HttpPhraseRepository implements PhraseRepository {
  constructor(private readonly client: ApiClient) {}

  async generate(pictogramIds: string[]): Promise<PhraseResponse> {
    return this.client.post('/phrases/generate', { pictogramIds }, PhraseResponseSchema)
  }
}
