export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  avatar?: string;
  is_admin: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Product {
  id: number;
  category_id: number;
  name: string;
  slug: string;
  description?: string;
  price: number;
  sale_price?: number;
  effective_price: number;
  discount_percentage: number;
  image?: string;
  images?: string[];
  brand?: string;
  sku: string;
  stock: number;
  is_featured: boolean;
  is_active: boolean;
  average_rating: number;
  total_reviews: number;
  has_reviews: boolean;
  is_low_stock: boolean;
  is_out_of_stock: boolean;
  created_at: string;
  updated_at: string;
  category?: Category;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image?: string;
  products_count?: number;
  created_at: string;
  updated_at: string;
}

export interface CartItem {
  id: number;
  user_id: number;
  product_id: number;
  quantity: number;
  size?: string;
  color?: string;
  product?: Product;
  created_at: string;
  updated_at: string;
}

export interface Cart {
  items: CartItem[];
  total: number;
  count: number;
}

export interface Order {
  id: number;
  user_id: number;
  order_number: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  subtotal: number;
  tax: number;
  shipping: number;
  discount: number;
  total: number;
  shipping_address: string;
  billing_address?: string;
  payment_method: string;
  payment_status: 'pending' | 'completed' | 'failed' | 'refunded';
  notes?: string;
  coupon_id?: number;
  coupon_code?: string;
  items: OrderItem[];
  user?: User;
  created_at: string;
  updated_at: string;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product_name: string;
  price: number;
  quantity: number;
  size?: string;
  color?: string;
  product?: Product;
  created_at: string;
  updated_at: string;
}

export interface Review {
  id: number;
  user_id: number;
  product_id: number;
  rating: number;
  title?: string;
  comment?: string;
  is_approved: boolean;
  is_verified_purchase: boolean;
  helpful_count: number;
  user?: Pick<User, 'id' | 'name'>;
  product?: Pick<Product, 'id' | 'name' | 'slug' | 'image'>;
  created_at: string;
  updated_at: string;
}

export interface WishlistItem {
  id: number;
  user_id: number;
  product_id: number;
  product?: Product;
  created_at: string;
  updated_at: string;
}

export interface Coupon {
  id: number;
  code: string;
  type: 'percentage' | 'fixed';
  value: number;
  minimum_order?: number;
  maximum_discount?: number;
  usage_limit?: number;
  used_count: number;
  is_active: boolean;
  starts_at?: string;
  expires_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Payment {
  id: number;
  order_id: number;
  transaction_id: string;
  provider: 'stripe' | 'paypal';
  provider_payment_id?: string;
  amount: number;
  currency: string;
  status: 'pending' | 'completed' | 'failed' | 'refunded' | 'partially_refunded';
  payment_method?: string;
  metadata?: Record<string, any>;
  receipt_url?: string;
  paid_at?: string;
  refunded_at?: string;
  created_at: string;
  updated_at: string;
}

export interface InventoryLog {
  id: number;
  product_id: number;
  user_id?: number;
  quantity: number;
  type: 'sale' | 'restock' | 'adjustment' | 'return' | 'correction';
  stock_before: number;
  stock_after: number;
  reference?: string;
  notes?: string;
  product?: Pick<Product, 'id' | 'name'>;
  user?: Pick<User, 'id' | 'name'>;
  created_at: string;
  updated_at: string;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}