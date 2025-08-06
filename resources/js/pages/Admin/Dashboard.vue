<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                :title="`Welcome, ${user.first_name}`" 
                description="Here's a snapshot of your company's activity on VenuePro."
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button as="a" :href="route('admin.users.create')" variant="default" size="sm" class="flex items-center gap-2">
                    <UserPlus class="h-4 w-4" />
                    Add User
                </Button>
                <Button as="a" :href="route('admin.rooms.create')" variant="outline" size="sm" class="flex items-center gap-2">
                    <MapPin class="h-4 w-4" />
                    Add Room
                </Button>
                <Button as="a" :href="route('admin.groups.index')" variant="outline" size="sm" class="flex items-center gap-2">
                    <Users class="h-4 w-4" />
                    Manage Groups
                </Button>
                <Button as="a" :href="route('admin.settings')" variant="outline" size="sm" class="flex items-center gap-2">
                    <Settings class="h-4 w-4" />
                    Company Settings
                </Button>
            </div>

            <!-- Company Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Users -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_users }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Across all roles in your company.
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Groups -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Groups</CardTitle>
                        <Users class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_groups }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            <span class="text-green-600">{{ stats.active_groups }} active</span>, 
                            <span class="text-orange-600">{{ stats.inactive_groups }} inactive</span>.
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Active Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.active_bookings }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Upcoming and ongoing bookings.
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Rooms -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Rooms</CardTitle>
                        <Building class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_rooms }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Across all buildings.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent_activities.length > 0" class="space-y-4">
                            <div v-for="activity in recent_activities" :key="activity.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <!-- Activity details here -->
                            </div>
                        </div>
                        <div v-else class="text-center py-10">
                            <p class="text-muted-foreground">No recent activity to display.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- User Management Overview -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">User Management</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-center py-10">
                            <p class="text-muted-foreground">User management overview coming soon.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import {
    UserPlus,
    MapPin,
    Settings,
    Users,
    Calendar,
    Building,
} from 'lucide-vue-next'
import { route } from 'ziggy-js'

interface User {
    first_name: string;
}

interface Stats {
    total_users: number;
    total_groups: number;
    active_groups: number;
    inactive_groups: number;
    active_bookings: number;
    total_rooms: number;
    pending_approvals: number;
}

interface Activity {
    id: number;
    description: string;
    created_at: string;
}

interface Props {
    user: User;
    stats: Stats;
    recent_activities: Activity[];
}

const props = defineProps<Props>();

</script>