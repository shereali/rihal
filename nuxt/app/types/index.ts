// Type definitions for Rihal

export interface Tenant {
  id: number
  name: string
  slug: string
  registration_number?: string | null
  address?: string | null
  contact_email: string
  contact_phone: string
  principal_name: string
  principal_email: string
  principal_phone: string
  logo_url?: string | null
  accent_color: string
  status: 'active' | 'suspended'
  settings?: Record<string, unknown> | null
  created_at: string
  updated_at: string
}

export interface User {
  id: number
  tenant_id: number | null
  email: string
  name: string
  name_bn?: string | null
  name_en?: string | null
  phone?: string | null
  role: 'super_admin' | 'tenant_admin' | 'teacher' | 'accountant' | 'staff'
  status: 'active' | 'inactive'
  avatar_url?: string | null
  settings?: Record<string, unknown> | null
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  success: boolean
  message?: string
  data: {
    user: User
    token: string
  }
}

export interface ApiError {
  success: false
  message: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  success: true
  data: T[]
  meta: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
}

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  name_bn: string
  name_en?: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  tenant_id?: string
}

// API Client types
export interface ApiClientConfig {
  baseUrl: string
  timeout: number
  headers: Record<string, string>
}
