<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading
                :title="`${user.first_name} ${user.last_name} - ${user.role?.name || user.user_type} `"
                :description="`${user.group?.name || 'Department'} management and team overview`"
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.team_members }}</div>
                        <p v-if="stats.team_members_change" class="text-xs flex items-center mt-1" :class="stats.team_members_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            <TrendingUp v-if="stats.team_members_change >= 0" class="h-3 w-3 mr-1" />
                            <TrendingDown v-else class="h-3 w-3 mr-1" />
                            {{ stats.team_members_change > 0 ? '+' : '' }}{{ stats.team_members_change }} this month
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.team_bookings }}</div>
                        <p v-if="stats.team_bookings_weekly" class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            {{ stats.team_bookings_weekly }} this week
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
                        <div class="text-3xl font-bold text-foreground">{{ stats.pending_approvals }}</div>
                        <p v-if="stats.pending_approvals > 0" class="text-xs text-orange-600 flex items-center mt-1">
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
                        <div class="text-3xl font-bold text-foreground">${{ stats.department_budget?.toLocaleString() }}</div>
                        <p v-if="stats.budget_utilization" class="text-xs flex items-center mt-1" :class="stats.budget_utilization > 80 ? 'text-red-600' : 'text-green-600'">
                            <TrendingUp v-if="stats.budget_utilization > 80" class="h-3 w-3 mr-1" />
                            <TrendingDown v-else class="h-3 w-3 mr-1" />
                            {{ stats.budget_utilization }}% utilized
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
                        <div class="space-y-4" v-if="teamActivity && teamActivity.length > 0">
                            <div v-for="activity in teamActivity" :key="activity.id"
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${activity.bgColor} rounded-full flex items-center justify-center`">
                                        <span :class="`${activity.textColor} font-semibold text-sm`">{{ activity.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ activity.user_name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ activity.action }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-foreground">{{ activity.formatted_time }}</p>
                                    <span :class="`text-xs px-2 py-1 rounded-full ${activity.status_class}`">
                                        {{ activity.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No recent team activity</p>
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
                        <div class="space-y-4" v-if="approvalRequests && approvalRequests.length > 0">
                            <div v-for="request in approvalRequests" :key="request.id"
                                 class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${request.bg_color} rounded-lg flex items-center justify-center`">
                                        <Calendar class="h-5 w-5" :class="request.text_color" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ request.room_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.user_name }} • {{ request.formatted_date }}</p>
                                        <p class="text-xs text-muted-foreground">{{ request.formatted_time }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <Button size="sm" variant="outline" class="h-8 px-3" @click="rejectRequest(request.id)">
                                        <X class="h-3 w-3" />
                                    </Button>
                                    <Button size="sm" variant="default" class="h-8 px-3" @click="approveRequest(request.id)">
                                        <Check class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <CheckCircle class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>No pending approval requests</p>
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
                            <div class="text-3xl font-bold text-green-600">{{ performance?.booking_efficiency || 0 }}%</div>
                            <p class="text-sm text-muted-foreground mt-1">Booking Efficiency</p>
                            <p v-if="performance?.booking_efficiency_change" class="text-xs mt-1" :class="performance.booking_efficiency_change >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ performance.booking_efficiency_change > 0 ? '+' : '' }}{{ performance.booking_efficiency_change }}% from last month
                            </p>
                        </div>

                        <!-- Team Utilization -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ performance?.team_utilization || 0 }}%</div>
                            <p class="text-sm text-muted-foreground mt-1">Team Utilization</p>
                            <p v-if="performance?.utilization_status" class="text-xs text-blue-600 mt-1">{{ performance.utilization_status }}</p>
                        </div>

                        <!-- Response Time -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ performance?.avg_response_time || 'N/A' }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Response Time</p>
                            <p v-if="performance?.response_time_trend" class="text-xs text-green-600 mt-1">{{ performance.response_time_trend }}</p>
                        </div>

                        <!-- Satisfaction Score -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">{{ performance?.team_satisfaction || 'N/A' }}</div>
                            <p class="text-sm text-muted-foreground mt-1">Team Satisfaction</p>
                            <p v-if="performance?.satisfaction_max" class="text-xs text-green-600 mt-1">Out of {{ performance.satisfaction_max }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
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
    stats: {
        team_members: number
        team_members_change?: number
        team_bookings: number
        team_bookings_weekly?: number
        pending_approvals: number
        department_budget?: number
        budget_utilization?: number
    }
    teamActivity?: Array<{
        id: number
        user_name: string
        action: string
        formatted_time: string
        status: string
        status_class: string
        initials: string
        bgColor: string
        textColor: string
    }>
    approvalRequests?: Array<{
        id: number
        room_name: string
        user_name: string
        formatted_date: string
        formatted_time: string
        bg_color: string
        text_color: string
    }>
    performance?: {
        booking_efficiency: number
        booking_efficiency_change?: number
        team_utilization: number
        utilization_status?: string
        avg_response_time: string
        response_time_trend?: string
        team_satisfaction: number | string
        satisfaction_max?: number
    }
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps<Props>()

// Methods for handling approval actions
const approveRequest = async (requestId: number) => {
    try {
        const response = await fetch(`/hod/dashboard/approve/${requestId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })

        if (response.ok) {
            // Refresh the page to update the data
            window.location.reload()
        } else {
            console.error('Failed to approve request')
        }
    } catch (error) {
        console.error('Error approving request:', error)
    }
}

const rejectRequest = async (requestId: number) => {
    try {
        const response = await fetch(`/hod/dashboard/reject/${requestId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })

        if (response.ok) {
            // Refresh the page to update the data
            window.location.reload()
        } else {
            console.error('Failed to reject request')
        }
    } catch (error) {
        console.error('Error rejecting request:', error)
    }
}
</script>
