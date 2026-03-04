<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import McpSetupController from '@/actions/App/Http/Controllers/Settings/McpSetupController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { show } from '@/routes/mcp-setup';
import { type BreadcrumbItem } from '@/types';

type Token = {
    id: number;
    name: string;
    created_at: string;
    last_used_at: string | null;
};

const props = defineProps<{
    mcpUrl: string;
    tokens: Token[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'MCP Setup',
        href: show().url,
    },
];

const page = usePage();
const flash = computed(
    () => (page.props.flash as { newToken?: string } | undefined) ?? {},
);

const copied = ref(false);
const copiedToken = ref(false);
const tokenName = ref('');
const errors = ref<{ name?: string }>({});
const processing = ref(false);

async function copyToClipboard(text: string, type: 'url' | 'token') {
    if (!text) return;

    try {
        await navigator.clipboard.writeText(text);
    } catch {
        // Fallback for non-HTTPS contexts
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }

    if (type === 'url') {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } else {
        copiedToken.value = true;
        setTimeout(() => {
            copiedToken.value = false;
        }, 2000);
    }
}

function createToken() {
    processing.value = true;
    errors.value = {};

    router.post(
        McpSetupController.store().url,
        { name: tokenName.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                tokenName.value = '';
            },
            onError: (errs) => {
                errors.value = errs as { name?: string };
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function revokeToken(tokenId: number) {
    if (!confirm('Are you sure you want to revoke this token?')) {
        return;
    }

    router.delete(McpSetupController.destroy(tokenId).url, {
        preserveScroll: true,
    });
}

const mcpConfig = computed(() =>
    JSON.stringify(
        {
            mcpServers: {
                donno: {
                    url: props.mcpUrl,
                    headers: {
                        Authorization: 'Bearer YOUR_TOKEN_HERE',
                    },
                },
            },
        },
        null,
        2,
    ),
);
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

                <!-- New Token Alert -->
                <Alert v-if="flash.newToken" variant="default" class="border-green-500 bg-green-50 dark:bg-green-950">
                    <AlertTitle class="text-green-800 dark:text-green-200">Token Created</AlertTitle>
                    <AlertDescription class="space-y-2">
                        <p class="text-green-700 dark:text-green-300">
                            Copy this token now. You won't be able to see it again.
                        </p>
                        <div class="flex items-center gap-2">
                            <code class="bg-green-100 dark:bg-green-900 px-2 py-1 rounded text-sm font-mono break-all">
                                {{ flash.newToken }}
                            </code>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="flash.newToken && copyToClipboard(flash.newToken, 'token')"
                            >
                                {{ copiedToken ? 'Copied!' : 'Copy' }}
                            </Button>
                        </div>
                    </AlertDescription>
                </Alert>

                <!-- Server URL -->
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="mcp-url">Donno MCP Server URL</Label>
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
                                @click="copyToClipboard(mcpUrl, 'url')"
                            >
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Config Example -->
                    <div class="bg-muted rounded-lg border p-4 font-mono text-sm">
                        <p class="text-muted-foreground mb-2">
                            Add this to your MCP client configuration:
                        </p>
                        <pre class="overflow-x-auto whitespace-pre-wrap">{{ mcpConfig }}</pre>
                    </div>
                </div>

                <Separator />

                <!-- Token Management -->
                <div class="space-y-4">
                    <Heading
                        variant="small"
                        title="API Tokens"
                        description="Create tokens to authenticate your MCP client"
                    />

                    <!-- Create Token Form -->
                    <form @submit.prevent="createToken" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="token-name">Token Name</Label>
                            <div class="flex items-center gap-2">
                                <Input
                                    id="token-name"
                                    v-model="tokenName"
                                    type="text"
                                    placeholder="e.g., Claude Code, Cursor"
                                    class="flex-1"
                                />
                                <Button type="submit" :disabled="processing || !tokenName">
                                    Create Token
                                </Button>
                            </div>
                            <InputError :message="errors.name" />
                        </div>
                    </form>

                    <!-- Token List -->
                    <div v-if="tokens.length > 0" class="space-y-2">
                        <div
                            v-for="token in tokens"
                            :key="token.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div>
                                <p class="font-medium">{{ token.name }}</p>
                                <p class="text-muted-foreground text-sm">
                                    Created {{ token.created_at }}
                                    <span v-if="token.last_used_at">
                                        · Last used {{ token.last_used_at }}
                                    </span>
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                @click="revokeToken(token.id)"
                            >
                                Revoke
                            </Button>
                        </div>
                    </div>

                    <p v-else class="text-muted-foreground text-sm">
                        No tokens created yet. Create one to authenticate your MCP client.
                    </p>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
