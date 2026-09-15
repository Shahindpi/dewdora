import { Category } from "./category";

export interface Brand {
  id: number;
  name: string;
  slug: string;
  logo: string | null;
}

export interface AffiliateProduct {
  id: number;

  name: string;
  slug: string;

  short_description: string | null;
  price: number | null;
  currency: string | null;

  rating: number | null;

  featured_image: string | null;

  brand?: Brand;
  category?: Category;
}