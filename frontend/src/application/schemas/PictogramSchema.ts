import { z } from 'zod'

export const PictogramSchema = z.object({
  id: z.string().uuid(),
  arasaacId: z.number().int().min(1),
  categoryId: z.string().uuid(),
  label: z.string().min(1).max(100),
  imagePath: z.string().min(1).max(500),
})

export const PictogramsArraySchema = z.array(PictogramSchema)

export type PictogramDTO = z.infer<typeof PictogramSchema>
