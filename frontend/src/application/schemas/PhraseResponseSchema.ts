import { z } from 'zod'

export const PhraseResponseSchema = z.object({
  variations: z.array(z.string().max(500)).min(1).max(3),
  source: z.enum(['cache', 'generated', 'fallback']),
  sequenceHash: z.string().min(1).max(128),
  pictogramIds: z.array(z.string().uuid()).min(1).max(10),
})

export type PhraseResponseDTO = z.infer<typeof PhraseResponseSchema>
