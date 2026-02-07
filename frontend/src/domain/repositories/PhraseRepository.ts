import type { PhraseResponse } from '@/domain/entities/PhraseResponse'

export interface PhraseRepository {
  generate(pictogramIds: string[]): Promise<PhraseResponse>
}
