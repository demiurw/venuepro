<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="Group Management" 
                description="Create, manage, and organize user groups within your company."
            />

            <!-- Group Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Total Groups</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ groupStats.total }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Active Groups</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ groupStats.active }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Total Members</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ totalMembers }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Group Table -->
            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>All Groups</CardTitle>
                    <Button as="a" :href="route('admin.groups.create')">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Group
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="group in groups.data" :key="group.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ group.name }}</div>
                                        <div class="text-sm text-gray-500">{{ group.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ group.members.length }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ group.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(group.created_at).toLocaleDateString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.groups.show', group.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { route } from 'ziggy-js'

interface Group {
    id: number;
    name: string;
    description: string;
    is_active: boolean;
    created_at: string;
    members: any[];
}

interface PaginatedGroups {
    data: Group[];
}

interface GroupStats {
    total: number;
    active: number;
    inactive: number;
}

interface Props {
    groups: PaginatedGroups;
    filters: {
        search: string;
        status: string;
    };
    groupStats: GroupStats;
    totalMembers: number;
}

const props = defineProps<Props>();
</script>
