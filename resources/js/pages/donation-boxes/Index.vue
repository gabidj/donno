<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
  donationBoxes: DonationBox[];
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Donation Boxes',
    href: '/donation-boxes',
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
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Donation Boxes" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Heading
          title="Your Donation Boxes"
          description="Manage your donation campaigns"
        />
        <Button as-child>
          <Link href="/donation-boxes/create">Create New</Link>
        </Button>
      </div>

      <div
        v-if="donationBoxes.length === 0"
        class="text-muted-foreground py-12 text-center"
      >
        <p>You haven't created any donation boxes yet.</p>
        <Button as-child class="mt-4">
          <Link href="/donation-boxes/create">Create your first one</Link>
        </Button>
      </div>

      <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="box in donationBoxes" :key="box.id">
          <CardHeader>
            <div class="flex items-start justify-between">
              <div>
                <CardTitle class="text-lg">{{ box.title }}</CardTitle>
                <CardDescription class="mt-1">
                  <span
                    :class="{
                      'text-green-600': box.status === 'open',
                      'text-red-600': box.status === 'closed',
                    }"
                  >
                    {{ box.status === 'open' ? 'Open' : 'Closed' }}
                  </span>
                  &middot;
                  <span class="capitalize">{{ box.visibility }}</span>
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-muted-foreground mb-4 line-clamp-2 text-sm">
              {{ box.purpose }}
            </p>

            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span>Raised</span>
                <span class="font-medium">
                  {{ formatAmount(box.current_amount, box.currency) }}
                  <span v-if="box.target_amount" class="text-muted-foreground">
                    / {{ formatAmount(box.target_amount, box.currency) }}
                  </span>
                </span>
              </div>

              <div
                v-if="box.target_amount"
                class="bg-secondary h-2 overflow-hidden rounded-full"
              >
                <div
                  class="bg-primary h-full transition-all"
                  :style="{
                    width: `${getProgressPercentage(box.current_amount, box.target_amount)}%`,
                  }"
                />
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <Button as-child variant="outline" size="sm">
                <Link :href="`/donation-boxes/${box.id}`">View</Link>
              </Button>
              <Button as-child variant="outline" size="sm">
                <Link :href="`/donation-boxes/${box.id}/edit`">Edit</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
