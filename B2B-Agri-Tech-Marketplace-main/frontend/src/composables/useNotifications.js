import { ref, computed, watchEffect, onMounted } from 'vue'
import { useOrders } from '@/composables/useOrders'
import { api } from '@/services/api'
import { useAuth } from '@/composables/useAuth'

const readNotificationIds = ref(new Set(JSON.parse(localStorage.getItem('qelem_read_notifications') || '[]')))
const dbNotifications = ref([])
const dbUnreadCount = ref(0)

export function useNotifications() {
    const { orders, refreshOrders } = useOrders()
    const { user } = useAuth()

    const fetchDbNotifications = async () => {
        try {
            const res = await api.getNotifications()
            if (res && res.notifications) {
                dbNotifications.value = res.notifications.map(n => ({
                    id: n.id,
                    title: n.data.title || 'System Alert',
                    message: n.data.message || 'You have a new notification.',
                    type: n.data.type || 'system',
                    isRead: n.read_at !== null,
                    timestamp: n.created_at || new Date().toISOString(),
                    orderId: n.data.order_number || null,
                    displayId: n.data.order_number || null,
                }))
                dbUnreadCount.value = res.unread_count || 0
            }
        } catch (e) {
            console.warn('Failed to fetch DB notifications', e)
        }
    }

    onMounted(() => {
        if (user.value) fetchDbNotifications()
    })

    const notifications = computed(() => {
        if (!orders.value || orders.value.length === 0) return []

        const list = []
        const isFarmer = user.value?.role === 'farmer'
        const isBuyer = user.value?.role === 'buyer'

        orders.value.forEach(order => {
            const displayId = order.displayId || order.id || order.order_number
            const dispute = order.dispute

            if (dispute) {
                const notifId = `dispute-${dispute.id}-${dispute.status}-${dispute.farmerResponse ? 'resp' : 'no'}-${dispute.resolutionNotes ? 'res' : 'no'}`

                // Dispute filed notification
                if (isFarmer && ['open', 'investigating'].includes(dispute.status)) {
                    list.push({
                        id: notifId,
                        orderId: order.id,
                        displayId,
                        disputeId: dispute.id,
                        title: dispute.farmerResponse ? 'Counter-Statement Submitted' : 'Buyer Filed Escrow Dispute',
                        message: dispute.description || 'Quality or transport exception raised.',
                        farmerResponse: dispute.farmerResponse,
                        status: dispute.status,
                        type: 'dispute',
                        timestamp: dispute.created_at || order.placed_at || new Date().toISOString(),
                        requiresResponse: !dispute.farmerResponse,
                        isRead: readNotificationIds.value.has(notifId)
                    })
                }

                // Admin resolution verdict notification for both buyer & farmer
                if (dispute.status === 'resolved' || dispute.resolutionNotes) {
                    const resNotifId = `resolution-${dispute.id}`
                    list.push({
                        id: resNotifId,
                        orderId: order.id,
                        displayId,
                        disputeId: dispute.id,
                        title: 'Admin Escrow Arbitrage Verdict Issued',
                        message: dispute.resolutionNotes || 'Arbitration decision has been finalized by platform admin.',
                        status: 'resolved',
                        type: 'resolution',
                        timestamp: dispute.updated_at || new Date().toISOString(),
                        isRead: readNotificationIds.value.has(resNotifId)
                    })
                }

                if (isBuyer && dispute.farmerResponse) {
                    const farmerRespId = `farmer-resp-${dispute.id}`
                    list.push({
                        id: farmerRespId,
                        orderId: order.id,
                        displayId,
                        disputeId: dispute.id,
                        title: 'Farmer Submitted Counter-Statement',
                        message: dispute.farmerResponse,
                        status: dispute.status,
                        type: 'farmer_response',
                        timestamp: dispute.updated_at || new Date().toISOString(),
                        isRead: readNotificationIds.value.has(farmerRespId)
                    })
                }
            }

            // Order status updates
            if (['dispatched', 'in_transit'].includes(order.status)) {
                const orderNotifId = `order-status-${order.id}-${order.status}`
                list.push({
                    id: orderNotifId,
                    orderId: order.id,
                    displayId,
                    title: `Order #${displayId} ${order.status === 'dispatched' ? 'Dispatched' : 'In Transit'}`,
                    message: isBuyer ? 'Farmer has dispatched your produce for transport.' : 'Fulfillment dispatched.',
                    status: order.status,
                    type: 'order',
                    timestamp: order.placed_at || new Date().toISOString(),
                    isRead: readNotificationIds.value.has(orderNotifId)
                })
            }
        })

        const allList = [...list, ...dbNotifications.value]
        return allList.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
    })

    const unreadCount = computed(() => {
        const localUnread = notifications.value.filter(n => !n.isRead && typeof n.id === 'string' && n.id.includes('-')).length
        return localUnread + dbUnreadCount.value
    })

    const markAsRead = async (id) => {
        const idStr = String(id)
        if (idStr.includes('-')) {
            readNotificationIds.value.add(idStr)
            localStorage.setItem('qelem_read_notifications', JSON.stringify([...readNotificationIds.value]))
        } else {
            // Wait to run it asynchronously to not block the UI
            api.markNotificationRead(idStr).then(() => {
                fetchDbNotifications()
            })
            // Optimistic update
            const item = dbNotifications.value.find(n => n.id === idStr)
            if (item) item.isRead = true
            dbUnreadCount.value = Math.max(0, dbUnreadCount.value - 1)
        }
    }

    const markAllAsRead = async () => {
        notifications.value.forEach(n => {
            const idStr = String(n.id)
            if (idStr.includes('-')) {
                readNotificationIds.value.add(idStr)
            }
        })
        localStorage.setItem('qelem_read_notifications', JSON.stringify([...readNotificationIds.value]))

        await api.markAllNotificationsRead()
        await fetchDbNotifications()
    }

    const submitDisputeReplyFromNotification = async (disputeId, replyText) => {
        await api.respondToPaymentException(disputeId, replyText)
        await refreshOrders()
    }

    return {
        notifications,
        unreadCount,
        markAsRead,
        markAllAsRead,
        submitDisputeReplyFromNotification,
        refreshNotifications: () => {
            refreshOrders()
            fetchDbNotifications()
        }
    }
}
