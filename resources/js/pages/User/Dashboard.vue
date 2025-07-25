<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <Heading 
                title="My Dashboard" 
                description="Your personal workspace for managing bookings and calendar"
            />

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <Button variant="default" size="sm" class="flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    Book Room
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    View Calendar
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <Clock class="h-4 w-4" />
                    My Bookings
                </Button>
                <Button variant="outline" size="sm" class="flex items-center gap-2">
                    <User class="h-4 w-4" />
                    Update Profile
                </Button>
            </div>

            <!-- User Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- My Bookings -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">My Bookings</CardTitle>
                        <Calendar class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.my_bookings || 12 }}</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            3 upcoming
                        </p>
                    </CardContent>
                </Card>

                <!-- Hours Booked -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Hours Booked</CardTitle>
                        <Clock class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.hours_booked || 24 }}h</div>
                        <p class="text-xs text-blue-600 flex items-center mt-1">
                            <Clock class="h-3 w-3 mr-1" />
                            This month
                        </p>
                    </CardContent>
                </Card>

                <!-- Favorite Rooms -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Favorite Rooms</CardTitle>
                        <Star class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.favorite_rooms || 3 }}</div>
                        <p class="text-xs text-muted-foreground flex items-center mt-1">
                            <MapPin class="h-3 w-3 mr-1" />
                            Conference A
                        </p>
                    </CardContent>
                </Card>

                <!-- Attendance Rate -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Attendance Rate</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-foreground">{{ stats.attendance_rate || 96 }}%</div>
                        <p class="text-xs text-green-600 flex items-center mt-1">
                            <TrendingUp class="h-3 w-3 mr-1" />
                            Excellent
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Bookings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Upcoming Bookings</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="booking in upcomingBookings" :key="booking.id" 
                                 class="flex items-center justify-between p-4 bg-secondary/50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${booking.bgColor} rounded-full flex items-center justify-center`">
                                        <Calendar class="h-5 w-5" :class="booking.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ booking.room }}</p>
                                        <p class="text-sm text-muted-foreground">{{ booking.date }} • {{ booking.time }}</p>
                                        <p class="text-xs text-muted-foreground">{{ booking.purpose }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-1">
                                    <span :class="`text-xs px-2 py-1 rounded-full ${booking.statusClass}`">
                                        {{ booking.status }}
                                    </span>
                                    <div class="flex space-x-1">
                                        <Button size="sm" variant="ghost" class="h-6 px-2 text-xs">
                                            Edit
                                        </Button>
                                        <Button size="sm" variant="ghost" class="h-6 px-2 text-xs text-red-600">
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                View All My Bookings
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Available Rooms -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Available Now</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="room in availableRooms" :key="room.id" 
                                 class="flex items-center justify-between p-4 border rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div :class="`h-10 w-10 ${room.bgColor} rounded-lg flex items-center justify-center`">
                                        <MapPin class="h-5 w-5" :class="room.textColor" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ room.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ room.building }} • Capacity: {{ room.capacity }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div v-for="amenity in room.amenities" :key="amenity" 
                                                 class="text-xs bg-secondary px-2 py-0.5 rounded">
                                                {{ amenity }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-green-600 mb-1">Available</div>
                                    <Button size="sm" variant="default" class="h-7 px-3 text-xs">
                                        Book Now
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <Button variant="ghost" size="sm" class="w-full">
                                Browse All Rooms
                                <MapPin class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Personal Insights -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">Personal Insights</CardTitle>
                    <p class="text-sm text-muted-foreground">Your booking patterns and preferences</p>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Most Booked Day -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">Tuesday</div>
                            <p class="text-sm text-muted-foreground mt-1">Most Booked Day</p>
                            <p class="text-xs text-blue-600 mt-1">32% of bookings</p>
                        </div>

                        <!-- Preferred Time -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">2-4 PM</div>
                            <p class="text-sm text-muted-foreground mt-1">Preferred Time</p>
                            <p class="text-xs text-green-600 mt-1">Most frequent slot</p>
                        </div>

                        <!-- Average Duration -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">1.5h</div>
                            <p class="text-sm text-muted-foreground mt-1">Avg Duration</p>
                            <p class="text-xs text-purple-600 mt-1">Per booking</p>
                        </div>

                        <!-- Success Rate -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">98%</div>
                            <p class="text-sm text-muted-foreground mt-1">Success Rate</p>
                            <p class="text-xs text-green-600 mt-1">Rarely cancelled</p>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="font-medium text-sm mb-3">Quick Tips</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-start space-x-2">
                                    <Info class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">Book in advance</p>
                                        <p class="text-xs text-blue-700 mt-1">Popular rooms fill up quickly, especially on Tuesdays</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-start space-x-2">
                                    <Star class="h-4 w-4 text-green-600 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <p class="text-sm font-medium text-green-900">Try new rooms</p>
                                        <p class="text-xs text-green-700 mt-1">Training Room C has great tech and is often available</p>
                                    </div>
                                </div>
                            </div>
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
    Plus,
    Calendar,
    Clock,
    User,
    TrendingUp,
    Star,
    MapPin,
    CheckCircle,
    ChevronRight,
    Info
} from 'lucide-vue-next'

interface Props {
    user: any
    stats?: {
        my_bookings: number
        hours_booked: number
        favorite_rooms: number
        attendance_rate: number
    }
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        my_bookings: 12,
        hours_booked: 24,
        favorite_rooms: 3,
        attendance_rate: 96
    })
})

// Mock data for upcoming bookings
const upcomingBookings = [
    {
        id: 1,
        room: 'Conference Room A',
        date: 'Today',
        time: '2:00 PM - 3:30 PM',
        purpose: 'Team Standup',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 2,
        room: 'Meeting Room B',
        date: 'Tomorrow',
        time: '10:00 AM - 11:00 AM',
        purpose: 'Client Call',
        status: 'Confirmed',
        statusClass: 'bg-green-100 text-green-700',
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 3,
        room: 'Training Room',
        date: 'Jan 28',
        time: '1:00 PM - 2:30 PM',
        purpose: 'Workshop Prep',
        status: 'Pending',
        statusClass: 'bg-yellow-100 text-yellow-700',
        bgColor: 'bg-yellow-100',
        textColor: 'text-yellow-600'
    }
]

// Mock data for available rooms
const availableRooms = [
    {
        id: 1,
        name: 'Conference Room B',
        building: 'Main Building',
        capacity: 12,
        amenities: ['Projector', 'WiFi'],
        bgColor: 'bg-blue-100',
        textColor: 'text-blue-600'
    },
    {
        id: 2,
        name: 'Meeting Room C',
        building: 'Main Building',
        capacity: 6,
        amenities: ['TV', 'Whiteboard'],
        bgColor: 'bg-green-100',
        textColor: 'text-green-600'
    },
    {
        id: 3,
        name: 'Quiet Room',
        building: 'Annex',
        capacity: 4,
        amenities: ['Phone', 'WiFi'],
        bgColor: 'bg-purple-100',
        textColor: 'text-purple-600'
    }
]
</script>