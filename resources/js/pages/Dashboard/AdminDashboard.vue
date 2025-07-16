<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading title="Admin Dashboard" description="Overview of company metrics and recent activity"></Heading>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Companies Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Total Companies</CardTitle>
                        <div class="h-4 w-4 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18"/>
                                <path d="M5 21V7l8-4v18"/>
                                <path d="M19 21V11l-6-4"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">42</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +3 this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Subscriptions Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Active Subscriptions</CardTitle>
                        <div class="h-4 w-4 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20m9-9H3"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">38</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            90% active rate
                        </p>
                    </CardContent>
                </Card>

                <!-- Bookings Today Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600">Bookings Today</CardTitle>
                        <div class="h-4 w-4 text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-gray-900">112</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            Peak: 2-4 PM
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold text-gray-900">Recent Company Signups</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-sm">TI</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Tech Innovations Ltd</p>
                                    <p class="text-sm text-gray-500">Premium Plan • 150 employees</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Jan 15, 2025</p>
                                <p class="text-xs text-green-600">Active</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-semibold text-sm">GS</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Global Solutions Inc</p>
                                    <p class="text-sm text-gray-500">Standard Plan • 75 employees</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Jan 14, 2025</p>
                                <p class="text-xs text-green-600">Active</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-purple-600 font-semibold text-sm">SM</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Smart Media Corp</p>
                                    <p class="text-sm text-gray-500">Enterprise Plan • 300 employees</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Jan 13, 2025</p>
                                <p class="text-xs text-yellow-600">Pending Setup</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-semibold text-sm">DC</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Digital Consulting Group</p>
                                    <p class="text-sm text-gray-500">Standard Plan • 45 employees</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Jan 12, 2025</p>
                                <p class="text-xs text-green-600">Active</p>
                            </div>
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
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

// Placeholder data
const statsData = {
    totalCompanies: 42,
    activeSubscriptions: 38,
    bookingsToday: 112
}

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
