import type { User } from './auth';

export type DonationBoxVisibility = 'public' | 'unlisted' | 'private';
export type DonationBoxStatus = 'open' | 'closed';

export interface DonationBox {
  id: number;
  user_id: number;
  title: string;
  purpose: string;
  target_amount: number | null;
  current_amount: number;
  currency: string;
  visibility: DonationBoxVisibility;
  status: DonationBoxStatus;
  created_at: string;
  updated_at: string;
  user?: User;
}

export interface SelectOption {
  value: string;
  label: string;
}
