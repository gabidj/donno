<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type DonationBox } from '@/types';

type Props = {
  donationBox: DonationBox;
  canEdit: boolean;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Donation Boxes',
    href: '/donation-boxes',
  },
  {
    title: props.donationBox.title,
    href: `/donation-boxes/${props.donationBox.id}`,
  },
];

function formatAmount(amount: number | null, currency: string): string {
  if (amount === null) {
    return 'Open-ended';
  }
  return new Intl.NumberFormat('ro-RO', {
    style: 'currency',
    currency: currency,
  }).format(amount);
}

function getProgressPercentage(current: number, target: number | null): number {
  if (target === null || target === 0) {
    return 0;
  }
  return Math.min(100, (current / target) * 100);
}

function deleteDonationBox() {
  if (confirm('Are you sure you want to delete this donation box?')) {
    router.delete(`/donation-boxes/${props.donationBox.id}`);
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head :title="donationBox.title" />

    <div class="mx-auto max-w-3xl space-y-6">
      <div class="flex items-start justify-between">
        <Heading :title="donationBox.title" :description="donationBox.purpose" />
        <div v-if="canEdit" class="flex gap-2">
          <Button as-child variant="outline">
            <Link :href="`/donation-boxes/${donationBox.id}/edit`">Edit</Link>
          </Button>
          <Button variant="destructive" @click="deleteDonationBox">
            Delete
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Campaign Details</CardTitle>
          <CardDescription>
            <span
              :class="{
                'text-green-600': donationBox.status === 'open',
                'text-red-600': donationBox.status === 'closed',
              }"
            >
              {{ donationBox.status === 'open' ? 'Open' : 'Closed' }}
            </span>
            &middot;
            <span class="capitalize">{{ donationBox.visibility }}</span>
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div>
            <h4 class="text-muted-foreground mb-1 text-sm font-medium">
              Purpose
            </h4>
            <p class="whitespace-pre-wrap">{{ donationBox.purpose }}</p>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-muted-foreground text-sm font-medium"
                >Progress</span
              >
              <span class="font-medium">
                {{ formatAmount(donationBox.current_amount, donationBox.currency) }}
                <span
                  v-if="donationBox.target_amount"
                  class="text-muted-foreground"
                >
                  / {{ formatAmount(donationBox.target_amount, donationBox.currency) }}
                </span>
              </span>
            </div>

            <div
              v-if="donationBox.target_amount"
              class="bg-secondary h-3 overflow-hidden rounded-full"
            >
              <div
                class="bg-primary h-full transition-all"
                :style="{
                  width: `${getProgressPercentage(donationBox.current_amount, donationBox.target_amount)}%`,
                }"
              />
            </div>

            <p
              v-if="donationBox.target_amount"
              class="text-muted-foreground text-sm"
            >
              {{
                getProgressPercentage(
                  donationBox.current_amount,
                  donationBox.target_amount,
                ).toFixed(1)
              }}% of goal reached
            </p>
          </div>

          <div class="border-t pt-4">
            <dl class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <dt class="text-muted-foreground">Currency</dt>
                <dd class="font-medium">{{ donationBox.currency }}</dd>
              </div>
              <div>
                <dt class="text-muted-foreground">Created</dt>
                <dd class="font-medium">
                  {{ new Date(donationBox.created_at).toLocaleDateString() }}
                </dd>
              </div>
            </dl>
          </div>
        </CardContent>
      </Card>

      <div class="flex gap-2">
        <Button as-child variant="outline">
          <Link href="/donation-boxes">Back to list</Link>
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
