<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="Department Dashboard" 
                description="Team management and departmental overview"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <Users class="h-4 w-4" />
                    View Team
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <CheckCircle class="h-4 w-4" />
                    Pending Approvals
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    Team Bookings
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <FileText class="h-4 w-4" />
                    Team Reports
                </Button>
            </div>

            <!-- Department Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Team Members -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Team Members</CardTitle>
                        <Users class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.team_members || 12 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            2 new this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Team Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Team Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.team_bookings || 34 }}</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            8 this week
                        </p>
                    </CardContent>
                </Card>

                <!-- Pending Approvals -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Pending Approvals</CardTitle>
                        <AlertCircle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_approvals || 5 }}</div>
                        <p class="text-xs text-orange-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            Requires action
                        </p>
                    </CardContent>
                </Card>

                <!-- Department Budget -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Department Budget</CardTitle>
                        <DollarSign class="h-4 w-4 text-purple-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">${{ (stats.department_budget || 8500).toLocaleString() }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingDown class="h-3 w-3 mr-1" />
                            72% utilized
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Team Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Team Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="activity in teamActivity" :key="activity.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${activity.bgColor} rounded-full flex items-center justify-center`">
                                        <span :class="`${activity.textColor} font-semibold text-sm`">{{ activity.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ activity.user }}</p>
                                        <p class="text-sm text-muted-foreground">{{ activity.action }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-foreground">{{ activity.time }}</p>
                                    <span :class="`text-xs px-2 py-1 rounded-full ${activity.statusClass}`">
                                        {{ activity.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All Team Activity
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Approval Requests -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Approval Requests</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="request in approvalRequests" :key="request.id" 
                                 class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${request.bgColor} rounded-lg flex items-center justify-center`">
                                        <Calendar class="h-5 w-5" :class="request.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ request.room }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.user }} • {{ request.date }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.time }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <Button size="sm" variant="outline" class="h-8 px-3">
                                        <X class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="default" class="h-8 px-3">
                                        <Check class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All Requests
                                <AlertCircle class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Team Performance Overview -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">Team Performance Overview</CardTitle>
                    <p class="text-sm text-muted-foreground">Key metrics for your department</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Booking Efficiency -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">89%</div>
                            <p class="text-sm text-muted-foreground mt-1">Booking Efficiency</p>
                            <p class="text-xs text-green-600 mt-1">+5% from last month</p>
                        </div>

                        <!-- Team Utilization -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">76%</div>
                            <p class="text-sm text-muted-foreground mt-1">Team Utilization</p>
                            <p class="text-xs text-blue-600 mt-1">Optimal range</p>
                        </div>

                        <!-- Response Time -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">2.1h</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Response Time</p>
                            <p class="text-xs text-green-600 mt-1">-30min improvement</p>
                        </div>

                        <!-- Satisfaction Score -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">4.8</div>
                            <p class="text-sm text-muted-foreground mt-1">Team Satisfaction</p>
                            <p class="text-xs text-green-600 mt-1">Out of 5.0</p>
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
    Users,
    CheckCircle,
    Calendar,
    FileText,
    TrendingUp,
    TrendingDown,
    Clock,
    AlertCircle,
    DollarSign,
    ChevronRight,
    X,
    Check
} from 'lucide-vue-next'

interface Props {
    user: any
    stats?: {
        team_members: number
        team_bookings: number
        pending_approvals: number
        department_budget: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        team_members: 12,
        team_bookings: 34,
        pending_approvals: 5,
        department_budget: 8500
    })
})

// Mock data for team activity
const teamActivity = [
    {
        id: 1,
        user: 'Alice Cooper',
        action: 'Booked Conference Room A',
        time: '2 hours ago',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        initials: 'AC',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 2,
        user: 'Bob Smith',
        action: 'Requested Meeting Room B',
        time: '4 hours ago',
        status: 'Pending',
        statusClass: 'bg-yellow-100 text-yellow-700',
        initials: 'BS',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 3,
        user: 'Carol Johnson',
        action: 'Cancelled Board Room booking',
        time: '1 day ago',
        status: 'Cancelled',
        statusClass: 'bg-red-100 text-red-700',
        initials: 'CJ',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    }
]

// Mock data for approval requests
const approvalRequests = [
    {
        id: 1,
        room: 'Conference Room A',
        user: 'David Wilson',
        date: 'Tomorrow',
        time: '2:00 PM - 4:00 PM',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 2,
        room: 'Board Room',
        user: 'Emma Davis',
        date: 'Jan 28',
        time: '10:00 AM - 12:00 PM',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 3,
        room: 'Training Room',
        user: 'Frank Miller',
        date: 'Jan 29',
        time: '1:00 PM - 3:00 PM',
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    }
]
</script>