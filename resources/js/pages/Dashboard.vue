<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { 
    Calendar, 
    MapPin, 
    Users, 
    TrendingUp, 
    Clock,
    CheckCircle,
    AlertCircle,
    Plus,
    ArrowRight,
    BarChart3
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const page = usePage();
const auth = computed(() => page.props.auth);

// Modern dashboard data (would come from props in real implementation)
const dashboardStats = [
    {
        title: 'Total Bookings',
        value: '2,847',
        change: '+12.5%',
        trend: 'up',
        icon: Calendar,
        color: 'primary'
    },
    {
        title: 'Active Venues',
        value: '24',
        change: '+2',
        trend: 'up',
        icon: MapPin,
        color: 'success'
    },
    {
        title: 'Registered Users',
        value: '1,249',
        change: '+18.2%',
        trend: 'up',
        icon: Users,
        color: 'accent'
    },
    {
        title: 'Revenue',
        value: '$12,847',
        change: '+8.1%',
        trend: 'up',
        icon: TrendingUp,
        color: 'warning'
    }
];

const recentBookings = [
    {
        id: 1,
        venue: 'Conference Room A',
        user: 'John Smith',
        date: '2024-01-15',
        time: '09:00 - 11:00',
        status: 'confirmed'
    },
    {
        id: 2,
        venue: 'Main Hall',
        user: 'Sarah Johnson',
        date: '2024-01-15',
        time: '14:00 - 17:00',
        status: 'pending'
    },
    {
        id: 3,
        venue: 'Meeting Room B',
        user: 'Mike Wilson',
        date: '2024-01-16',
        time: '10:00 - 12:00',
        status: 'confirmed'
    }
];

const quickActions = [
    {
        title: 'New Booking',
        description: 'Create a new venue booking',
        icon: Plus,
        action: '/bookings/create',
        color: 'primary'
    },
    {
        title: 'Add Venue',
        description: 'Register a new venue',
        icon: MapPin,
        action: '/venues/create',
        color: 'success'
    },
    {
        title: 'View Reports',
        description: 'Access analytics and reports',
        icon: BarChart3,
        action: '/reports',
        color: 'accent'
    }
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Welcome Header -->
            <div class="space-y-2">
                <h1 class="text-h2 text-foreground">Welcome back, {{ auth.user.first_name }}!</h1>
                <p class="text-body text-muted-foreground">
                    Here's what's happening with your venue management today.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div 
                    v-for="stat in dashboardStats" 
                    :key="stat.title"
                    class="card-modern hover-lift p-6 bg-card"
                >
                    <div class="flex items-center justify-between">
                        <div class="space-y-2">
                            <p class="text-caption text-muted-foreground font-medium uppercase tracking-wider">
                                {{ stat.title }}
                            </p>
                            <p class="text-h3 font-semibold text-foreground">{{ stat.value }}</p>
                            <div class="flex items-center gap-1">
                                <TrendingUp class="h-3 w-3 text-success" />
                                <span class="text-caption text-success font-medium">{{ stat.change }}</span>
                                <span class="text-caption text-muted-foreground">vs last month</span>
                            </div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 border border-primary/20">
                            <component :is="stat.icon" class="h-6 w-6 text-primary" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Bookings -->
                <div class="lg:col-span-2">
                    <div class="card-modern p-6 bg-card">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-h4 font-semibold text-foreground">Recent Bookings</h3>
                                <p class="text-body-sm text-muted-foreground">Latest venue reservations</p>
                            </div>
                            <Button variant="outline" size="sm" class="gap-2">
                                View All
                                <ArrowRight class="h-3 w-3" />
                            </Button>
                        </div>
                        <div class="space-y-4">
                            <div 
                                v-for="booking in recentBookings" 
                                :key="booking.id"
                                class="flex items-center gap-4 p-4 rounded-lg border border-border/50 hover:border-border transition-colors"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted/50">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-body-sm font-medium text-foreground">{{ booking.venue }}</p>
                                        <span 
                                            :class="{
                                                'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-success/10 text-success': booking.status === 'confirmed',
                                                'bg-warning/10 text-warning': booking.status === 'pending'
                                            }"
                                        >
                                            <CheckCircle v-if="booking.status === 'confirmed'" class="h-3 w-3" />
                                            <Clock v-else class="h-3 w-3" />
                                            {{ booking.status }}
                                        </span>
                                    </div>
                                    <p class="text-caption text-muted-foreground">
                                        {{ booking.user }} • {{ booking.date }} • {{ booking.time }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-6">
                    <div class="card-modern p-6 bg-card">
                        <h3 class="text-h4 font-semibold text-foreground mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <Button
                                v-for="action in quickActions"
                                :key="action.title"
                                variant="ghost"
                                class="w-full justify-start h-auto p-4 hover:bg-secondary/50"
                            >
                                <div class="flex items-center gap-3 w-full">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                                        <component :is="action.icon" class="h-4 w-4 text-primary" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-body-sm font-medium text-foreground">{{ action.title }}</p>
                                        <p class="text-caption text-muted-foreground">{{ action.description }}</p>
                                    </div>
                                </div>
                            </Button>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="card-modern p-6 bg-card">
                        <h3 class="text-h4 font-semibold text-foreground mb-4">System Status</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 bg-success rounded-full"></div>
                                <span class="text-body-sm text-foreground">All systems operational</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 bg-success rounded-full"></div>
                                <span class="text-body-sm text-foreground">Database healthy</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-2 bg-warning rounded-full"></div>
                                <span class="text-body-sm text-foreground">Scheduled maintenance</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
