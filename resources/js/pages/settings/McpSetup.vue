<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/mcp-setup';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    mcpUrl: string;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'MCP Setup',
        href: show().url,
    },
];

const copied = ref(false);

function copyToClipboard() {
    navigator.clipboard.writeText(props.mcpUrl);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="MCP Setup" />

        <h1 class="sr-only">MCP Setup</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="MCP Setup"
                    description="Configure your MCP client to connect to the Donno MCP server"
                />

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label
                            for="mcp-url"
                            class="text-sm font-medium leading-none"
                        >
                            Donno MCP Server URL
                        </label>
                        <div class="flex items-center gap-2">
                            <Input
                                id="mcp-url"
                                type="text"
                                :model-value="mcpUrl"
                                readonly
                                class="font-mono text-sm"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                @click="copyToClipboard"
                            >
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </Button>
                        </div>
                        <p class="text-muted-foreground text-sm">
                            Use this URL to connect your MCP client to the Donno
                            server.
                        </p>
                    </div>

                    <div
                        class="bg-muted rounded-lg border p-4 font-mono text-sm"
                    >
                        <p class="text-muted-foreground mb-2">
                            Add this to your MCP client configuration:
                        </p>
                        <pre class="overflow-x-auto whitespace-pre-wrap">{{
                            JSON.stringify(
                                {
                                    mcpServers: {
                                        donno: {
                                            url: mcpUrl,
                                        },
                                    },
                                },
                                null,
                                2,
                            )
                        }}</pre>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>