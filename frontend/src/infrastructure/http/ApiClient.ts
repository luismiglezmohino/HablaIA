import type { ZodSchema } from 'zod'

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: { error: string; retryAfter?: number },
  ) {
    super(body.error)
    this.name = 'ApiError'
  }
}

function parseErrorBody(raw: unknown): { error: string; retryAfter?: number } {
  if (
    typeof raw === 'object' &&
    raw !== null &&
    'error' in raw &&
    typeof (raw as Record<string, unknown>).error === 'string'
  ) {
    const obj = raw as Record<string, unknown>
    return {
      error: obj.error as string,
      retryAfter: typeof obj.retryAfter === 'number' ? obj.retryAfter : undefined,
    }
  }
  return { error: 'Unknown server error' }
}

export class ApiClient {
  constructor(private readonly baseUrl: string = '/api') {}

  private async fetchWithRetry(url: string, options: RequestInit): Promise<Response> {
    try {
      return await fetch(url, options)
    } catch (error) {
      if (error instanceof TypeError) {
        return await fetch(url, options)
      }
      throw error
    }
  }

  async get<T>(path: string, schema: ZodSchema<T>): Promise<T> {
    const response = await this.fetchWithRetry(`${this.baseUrl}${path}`, { method: 'GET' })

    if (!response.ok) {
      let raw: unknown
      try {
        raw = await response.json()
      } catch {
        raw = null
      }
      throw new ApiError(response.status, parseErrorBody(raw))
    }

    const data = await response.json()
    return schema.parse(data)
  }

  async post<T>(path: string, body: unknown, schema: ZodSchema<T>): Promise<T> {
    const response = await this.fetchWithRetry(`${this.baseUrl}${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })

    if (!response.ok) {
      let raw: unknown
      try {
        raw = await response.json()
      } catch {
        raw = null
      }
      throw new ApiError(response.status, parseErrorBody(raw))
    }

    const data = await response.json()
    return schema.parse(data)
  }
}
