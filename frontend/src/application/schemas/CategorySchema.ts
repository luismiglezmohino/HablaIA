import { z } from 'zod'

export const CategorySchema = z.object({
  id: z.string().uuid(),
  name: z.string().min(1).max(50),
  icon: z.string().nullable(),
  colorHex: z.string().regex(/^#[0-9A-Fa-f]{6}$/),
  displayOrder: z.number().int().min(0),
})

export const CategoriesArraySchema = z.array(CategorySchema)

export type CategoryDTO = z.infer<typeof CategorySchema>
