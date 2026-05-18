@props([
    'pageType' => 'reports',
])

<div x-data="chatbot()" x-init="initChatbot()" class="fixed bottom-6 right-6 z-50">
    <!-- Chat Button -->
    <button 
        @click="toggleChat()" 
        class="w-16 h-16 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-full shadow-lg shadow-[#74c365]/40 hover:shadow-xl hover:shadow-[#74c365]/50 transition-all duration-300 flex items-center justify-center group"
        :class="{ 'rotate-180': isOpen }"
    >
        <template x-if="isOpen">
            <i class="fas fa-times text-white text-2xl"></i>
        </template>
        <template x-if="!isOpen">
            <div class="relative">
                <i class="fas fa-robot text-white text-2xl group-hover:scale-110 transition-transform"></i>
                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"></span>
            </div>
        </template>
    </button>

    <!-- Chat Window -->
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="absolute bottom-20 right-0 w-96 h-[500px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
    >
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#74c365] to-[#5dad4f] p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold">AI Support Assistant</h3>
                    <p class="text-white/80 text-xs">Powered by RGV Multi-Tech</p>
                </div>
            </div>
            <button @click="clearChat()" class="text-white/80 hover:text-white transition-colors" title="Clear Chat">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900" id="chatMessages">
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.type === 'user' ? 'justify-end' : 'justify-start'" class="flex">
                    <div :class="message.type === 'user' ? 'bg-[#74c365] text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none'" class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm">
                        <template x-if="message.type === 'bot'">
                            <div class="flex items-start space-x-2">
                                <div class="w-6 h-6 bg-[#74c365]/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-robot text-[#74c365] text-xs"></i>
                                </div>
                                <div>
                                    <div x-html="formatMessage(message.content)"></div>
                                    <span x-text="formatTime(message.timestamp)" class="text-xs opacity-70 mt-1 block"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="message.type === 'user'">
                            <div>
                                <div x-text="message.content"></div>
                                <span x-text="formatTime(message.timestamp)" class="text-xs opacity-70 mt-1 block"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            
            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white rounded-2xl rounded-bl-none px-4 py-3 shadow-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-[#74c365]/10 rounded-full flex items-center justify-center">
                            <i class="fas fa-robot text-[#74c365] text-xs"></i>
                        </div>
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div x-show="messages.length === 0" class="p-3 border-t border-gray-200 bg-white">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Quick Actions:</p>
            <div class="flex flex-wrap gap-2">
                <button @click="sendQuickMessage('Show me booking summary')" class="px-3 py-1.5 bg-[#f0f9ef] text-[#74c365] rounded-full text-xs hover:bg-[#e0f3df] transition-colors">
                    📊 Booking Summary
                </button>
                <button @click="sendQuickMessage('What is the inventory status?')" class="px-3 py-1.5 bg-[#f0f9ef] text-[#74c365] rounded-full text-xs hover:bg-[#e0f3df] transition-colors">
                    📦 Inventory Status
                </button>
                <button @click="sendQuickMessage('How do I export reports?')" class="px-3 py-1.5 bg-[#f0f9ef] text-[#74c365] rounded-full text-xs hover:bg-[#e0f3df] transition-colors">
                    📥 Export Reports
                </button>
                <button @click="sendQuickMessage('Track borrow requests')" class="px-3 py-1.5 bg-[#f0f9ef] text-[#74c365] rounded-full text-xs hover:bg-[#e0f3df] transition-colors">
                    🔍 Track Requests
                </button>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <form @submit.prevent="sendMessage()" class="flex items-center space-x-2">
                <input 
                    type="text" 
                    x-model="inputMessage" 
                    @keydown.enter="sendMessage()"
                    placeholder="Ask me about reports..."
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#74c365]/50 focus:border-[#74c365] text-sm"
                >
                <button 
                    type="submit"
                    :disabled="!inputMessage.trim() || isTyping"
                    class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center text-white hover:shadow-lg hover:shadow-[#74c365]/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function chatbot() {
    return {
        isOpen: false,
        isTyping: false,
        inputMessage: '',
        unreadCount: 0,
        messages: [],
        pageType: '{{ $pageType }}',

        initChatbot() {
            // Load chat history from localStorage
            const saved = localStorage.getItem('chatbot_history');
            if (saved) {
                this.messages = JSON.parse(saved);
            } else {
                // Add welcome message
                this.addBotMessage("Hello! I'm your AI Support Assistant. I can help you with:\n\n• 📊 Report analysis and summaries\n• 📦 Inventory insights\n• 🔍 Borrow request tracking\n• 👥 User statistics\n• 📥 Exporting reports\n\nHow can I assist you today?");
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.unreadCount = 0;
                this.scrollToBottom();
            }
        },

        async sendMessage() {
            if (!this.inputMessage.trim() || this.isTyping) return;

            const userMessage = this.inputMessage.trim();
            this.addUserMessage(userMessage);
            this.inputMessage = '';
            this.isTyping = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch('/api/chatbot/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        message: userMessage,
                        pageType: this.pageType,
                        history: this.messages.slice(-5) // Send last 5 messages for context
                    })
                });

                const data = await response.json().catch(() => ({
                    success: false,
                    response: 'The chatbot endpoint returned an invalid response.'
                }));
                
                if (response.ok && data.success) {
                    this.addBotMessage(data.response);
                } else {
                    this.addBotMessage("I apologize, but I'm having trouble processing your request right now. Please try again or contact support for assistance.");
                }
            } catch (error) {
                console.error('Chatbot error:', error);
                this.addBotMessage("I'm experiencing some technical difficulties. Please try again later or contact our support team directly.");
            } finally {
                this.isTyping = false;
                this.saveHistory();
            }
        },

        sendQuickMessage(message) {
            this.inputMessage = message;
            this.sendMessage();
        },

        addUserMessage(content) {
            this.messages.push({
                type: 'user',
                content: content,
                timestamp: new Date().toISOString()
            });
            this.scrollToBottom();
        },

        addBotMessage(content) {
            this.messages.push({
                type: 'bot',
                content: content,
                timestamp: new Date().toISOString()
            });
            this.scrollToBottom();
            
            if (!this.isOpen) {
                this.unreadCount++;
            }
        },

        clearChat() {
            this.messages = [];
            this.addBotMessage("Hello! I'm your AI Support Assistant. I can help you with:\n\n• 📊 Report analysis and summaries\n• 📦 Inventory insights\n• 🔍 Borrow request tracking\n• 👥 User statistics\n• 📥 Exporting reports\n\nHow can I assist you today?");
            this.saveHistory();
        },

        saveHistory() {
            localStorage.setItem('chatbot_history', JSON.stringify(this.messages.slice(-20))); // Keep last 20 messages
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('chatMessages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        formatMessage(content) {
            // Convert markdown-like syntax to HTML
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>')
                .replace(/• /g, '&bull; ');
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    };
}
</script>
}
    };
}
</script>
  };
}
</script>
}
    };
}
</script>
 new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    };
}
</script>
}
    };
}
</script>
