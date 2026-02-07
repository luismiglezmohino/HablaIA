export type PhraseSource = 'cache' | 'generated' | 'fallback'

export interface PhraseResponse {
  readonly variations: string[]
  readonly source: PhraseSource
  readonly sequenceHash: string
  readonly pictogramIds: string[]
}
