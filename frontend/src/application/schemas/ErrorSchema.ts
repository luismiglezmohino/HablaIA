import { z } from 'zod'

export const ErrorResponseSchema = z.object({
  error: z.string().min(1),
})

export const RateLimitErrorSchema = z.object({
  error: z.string().min(1),
  retryAfter: z.number().int(),
})

export type ErrorResponseDTO = z.infer<typeof ErrorResponseSchema>
export type RateLimitErrorDTO = z.infer<typeof RateLimitErrorSchema>
