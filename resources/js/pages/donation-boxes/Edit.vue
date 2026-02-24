<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import {
  type BreadcrumbItem,
  type DonationBox,
  type SelectOption,
} from '@/types';

type Props = {
  donationBox: DonationBox;
  visibilities: SelectOption[];
  statuses: SelectOption[];
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
  {
    title: 'Edit',
    href: `/donation-boxes/${props.donationBox.id}/edit`,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head :title="`Edit: ${donationBox.title}`" />

    <div class="mx-auto max-w-2xl space-y-6">
      <Heading
        title="Edit Donation Box"
        :description="`Editing: ${donationBox.title}`"
      />

      <Form
        method="patch"
        :action="`/donation-boxes/${donationBox.id}`"
        class="space-y-6"
        v-slot="{ errors, processing, recentlySuccessful }"
      >
        <div class="grid gap-2">
          <Label for="title">Title</Label>
          <Input
            id="title"
            name="title"
            required
            :default-value="donationBox.title"
            placeholder="Give your campaign a name"
          />
          <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
          <Label for="purpose">Purpose</Label>
          <Textarea
            id="purpose"
            name="purpose"
            required
            rows="4"
            :default-value="donationBox.purpose"
            placeholder="Describe the purpose of this donation campaign..."
          />
          <InputError :message="errors.purpose" />
        </div>

        <div class="grid gap-2">
          <Label for="target_amount">Target Amount (optional)</Label>
          <Input
            id="target_amount"
            name="target_amount"
            type="number"
            min="1"
            step="0.01"
            :default-value="donationBox.target_amount?.toString() ?? ''"
            placeholder="Leave empty for open-ended"
          />
          <p class="text-muted-foreground text-sm">
            Amount is in RON. Leave empty for an open-ended campaign.
          </p>
          <InputError :message="errors.target_amount" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label for="visibility">Visibility</Label>
            <Select name="visibility" :default-value="donationBox.visibility">
              <SelectTrigger id="visibility">
                <SelectValue placeholder="Select visibility" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="option in visibilities"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="errors.visibility" />
          </div>

          <div class="grid gap-2">
            <Label for="status">Status</Label>
            <Select name="status" :default-value="donationBox.status">
              <SelectTrigger id="status">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="option in statuses"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="errors.status" />
          </div>
        </div>

        <div class="flex items-center gap-4">
          <Button :disabled="processing">Save Changes</Button>
          <Button as-child variant="outline">
            <Link :href="`/donation-boxes/${donationBox.id}`">Cancel</Link>
          </Button>

          <Transition
            enter-active-class="transition ease-in-out"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out"
            leave-to-class="opacity-0"
          >
            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
              Saved.
            </p>
          </Transition>
        </div>
      </Form>
    </div>
  </AppLayout>
</template>
