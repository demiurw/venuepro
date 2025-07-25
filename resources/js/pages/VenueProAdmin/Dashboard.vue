<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="VenuePro Admin Dashboard" 
                description="System-wide overview and management of all companies and tenants"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <Building class="h-4 w-4" />
                    Add Company
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Shield class="h-4 w-4" />
                    System Health
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <BarChart3 class="h-4 w-4" />
                    Global Analytics
                </Button>
            </div>

            <!-- System Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Companies -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Companies</CardTitle>
                        <Building class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_companies }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            +3 this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Users -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Active Users</CardTitle>
                        <Users class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.active_users }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            92% active rate
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.total_bookings }}</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            +25% vs last month
                        </p>
                    </CardContent>
                </Card>

                <!-- System Revenue -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">System Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-emerald-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">${{ (stats.system_revenue || 45280).toLocaleString() }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            +18% MRR growth
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Company Signups -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Recent Company Signups</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="company in recentSignups" :key="company.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${company.bgColor} rounded-full flex items-center justify-center`">
                                        <span :class="`${company.textColor} font-semibold text-sm`">{{ company.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ company.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ company.plan }} • {{ company.employees }} employees</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-foreground">{{ company.signupDate }}</p>
                                    <span :class="`text-xs px-2 py-1 rounded-full ${company.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`">
                                        {{ company.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All Companies
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- System Health Overview -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">System Health</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <!-- Server Status -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium">Server Status</span>
                                </div>
                                <span class="text-sm text-green-600">Healthy</span>
                            </div>

                            <!-- Database -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium">Database</span>
                                </div>
                                <span class="text-sm text-green-600">Online</span>
                            </div>

                            <!-- Email Service -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium">Email Service</span>
                                </div>
                                <span class="text-sm text-green-600">Operational</span>
                            </div>

                            <!-- Queue Jobs -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-yellow-500 rounded-full"></div>
                                    <span class="text-sm font-medium">Queue Jobs</span>
                                </div>
                                <span class="text-sm text-yellow-600">23 pending</span>
                            </div>

                            <!-- Storage -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-3 w-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium">Storage</span>
                                </div>
                                <span class="text-sm text-green-600">78% used</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View System Logs
                                <ExternalLink class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Platform Analytics -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">Platform Usage Analytics</CardTitle>
                    <p class="text-sm text-muted-foreground">Key metrics across all companies</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">94.2%</div>
                            <p class="text-sm text-muted-foreground mt-1">Platform Uptime</p>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">8.7s</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Response Time</p>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">2,847</div>
                            <p class="text-sm text-muted-foreground mt-1">Daily Active Users</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { withDefaults } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import {
    Building,
    Shield,
    BarChart3,
    TrendingUp,
    Users,
    Calendar,
    Clock,
    DollarSign,
    ChevronRight,
    ExternalLink
} from 'lucide-vue-next'

interface Props {
    user: any
    stats: {
        total_companies: number
        active_users: number
        total_bookings: number
        system_revenue: number
    }
    company_analytics: any[]
    recent_signups: any[]
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_companies: 42,
        active_users: 235,
        total_bookings: 1247,
        system_revenue: 45280
    }),
    company_analytics: () => [],
    recent_signups: () => []
})

// Mock data for recent signups (replace with real data from props)
const recentSignups = [
    {
        id: 1,
        name: 'Tech Innovations Ltd',
        plan: 'Premium Plan',
        employees: 150,
        signupDate: 'Jan 15, 2025',
        status: 'Active',
        initials: 'TI',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 2,
        name: 'Global Solutions Inc',
        plan: 'Standard Plan',
        employees: 75,
        signupDate: 'Jan 14, 2025',
        status: 'Active',
        initials: 'GS',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 3,
        name: 'Smart Media Corp',
        plan: 'Enterprise Plan',
        employees: 300,
        signupDate: 'Jan 13, 2025',
        status: 'Pending Setup',
        initials: 'SM',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    },
    {
        id: 4,
        name: 'Digital Consulting Group',
        plan: 'Standard Plan',
        employees: 45,
        signupDate: 'Jan 12, 2025',
        status: 'Active',
        initials: 'DC',
        bgColor: 'bg-orange-100',
        textColor: 'text-orange-600'
    }
]
</script>