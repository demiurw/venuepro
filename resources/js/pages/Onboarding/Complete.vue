<template>
    <AuthLayout>
        <div class="min-h-screen bg-gradient-to-br from-success/5 via-background to-primary/5 flex items-center justify-center">
            <div class="container mx-auto px-6 py-12">
                <div class="max-w-2xl mx-auto text-center">
                    <!-- Success Animation -->
                    <div class="mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-success/10 rounded-full mb-6 animate-pulse">
                            <CheckCircle class="h-12 w-12 text-success animate-bounce" />
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div class="space-y-6 mb-12">
                        <h1 class="text-4xl font-bold text-foreground">
                            🎉 Congratulations!
                        </h1>
                        <h2 class="text-2xl font-semibold text-foreground">
                            Your VenuePro Setup is Complete
                        </h2>
                        <p class="text-xl text-muted-foreground leading-relaxed">
                            You've successfully configured your venue management system. 
                            Your team can now start booking rooms and managing spaces efficiently.
                        </p>
                    </div>

                    <!-- Setup Summary -->
                    <Card class="mb-12 text-left">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3">
                                <Settings class="h-5 w-5 text-primary" />
                                Setup Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-if="summary.buildings > 0" class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-primary/10 rounded-lg">
                                        <Building2 class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ summary.buildings }} Buildings</p>
                                        <p class="text-sm text-muted-foreground">Locations added</p>
                                    </div>
                                </div>

                                <div v-if="summary.rooms > 0" class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-primary/10 rounded-lg">
                                        <DoorOpen class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ summary.rooms }} Rooms</p>
                                        <p class="text-sm text-muted-foreground">Bookable spaces</p>
                                    </div>
                                </div>

                                <div v-if="summary.groups > 0" class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-primary/10 rounded-lg">
                                        <Users class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ summary.groups }} Groups</p>
                                        <p class="text-sm text-muted-foreground">Departments & teams</p>
                                    </div>
                                </div>

                                <div v-if="summary.users > 0" class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-primary/10 rounded-lg">
                                        <UserPlus class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ summary.users }} Users</p>
                                        <p class="text-sm text-muted-foreground">Team members invited</p>
                                    </div>
                                </div>

                                <div v-if="summary.labels > 0" class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 bg-primary/10 rounded-lg">
                                        <Tags class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ summary.labels }} Labels</p>
                                        <p class="text-sm text-muted-foreground">Booking categories</p>
                                    </div>
                                </div>

                                <!-- OTP Reminder if users were added -->
                                <div v-if="summary.users > 0" class="md:col-span-2 p-4 bg-info/10 border border-info/20 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <Mail class="h-5 w-5 text-info mt-0.5" />
                                        <div>
                                            <p class="font-medium text-info">OTP Verification Emails Sent</p>
                                            <p class="text-sm text-info/80">
                                                Your team members will receive OTP verification emails shortly. 
                                                They need to verify their accounts before accessing VenuePro.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Next Steps -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-foreground">What's Next?</h3>
                        
                        <div class="grid gap-4 text-left">
                            <Card class="hover:border-primary/30 transition-colors cursor-pointer" @click="goToDashboard">
                                <CardContent class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center w-12 h-12 bg-primary/10 rounded-lg">
                                            <BarChart3 class="h-6 w-6 text-primary" />
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-foreground">Explore Your Dashboard</h4>
                                            <p class="text-muted-foreground">View analytics, manage bookings, and monitor your system</p>
                                        </div>
                                        <ArrowRight class="h-5 w-5 text-muted-foreground ml-auto" />
                                    </div>
                                </CardContent>
                            </Card>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Card class="hover:border-primary/30 transition-colors">
                                    <CardContent class="p-4">
                                        <div class="flex items-center gap-3">
                                            <Calendar class="h-5 w-5 text-primary" />
                                            <div>
                                                <h5 class="font-medium text-foreground">Make Your First Booking</h5>
                                                <p class="text-sm text-muted-foreground">Test the booking system</p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

                                <Card class="hover:border-primary/30 transition-colors">
                                    <CardContent class="p-4">
                                        <div class="flex items-center gap-3">
                                            <Settings class="h-5 w-5 text-primary" />
                                            <div>
                                                <h5 class="font-medium text-foreground">Customize Settings</h5>
                                                <p class="text-sm text-muted-foreground">Adjust preferences</p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-8">
                            <Button 
                                @click="goToDashboard"
                                size="lg"
                                class="flex items-center gap-3 h-14 px-8 rounded-xl text-lg font-medium"
                            >
                                <Zap class="h-5 w-5" />
                                Go to Your Dashboard
                                <ArrowRight class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import {
    CheckCircle,
    Settings,
    Building2,
    DoorOpen,
    Users,
    UserPlus,
    Tags,
    Mail,
    BarChart3,
    ArrowRight,
    Calendar,
    Zap
} from 'lucide-vue-next'

interface SetupSummary {
    buildings: number
    rooms: number
    groups: number
    users: number
    labels: number
}

interface Props {
    summary?: SetupSummary
}

const props = withDefaults(defineProps<Props>(), {
    summary: () => ({
        buildings: 0,
        rooms: 0,
        groups: 0,
        users: 0,
        labels: 0
    })
})

// Methods
const goToDashboard = () => {
    router.get('/admin/dashboard')
}
</script>