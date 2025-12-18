<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/components/ui/button";
import {
  GraduationCap,
  TrendingUp,
  Users,
  BarChart3,
  CheckCircle2,
  ArrowRight,
  Sparkles,
  Target,
  Award,
  Menu,
  X,
} from "lucide-vue-next";

const router = useRouter();
const isVisible = ref(false);
const isScrolled = ref(false);
const mobileMenuOpen = ref(false);
const heroImage = new URL("@/assets/img/hero-islamic.png", import.meta.url)
  .href;

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 100);

  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});

function handleScroll() {
  isScrolled.value = window.scrollY > 100;
}

const features = [
  {
    icon: Users,
    title: "Alumni Tracking",
    description:
      "Monitor alumni career journeys with an integrated system that provides comprehensive insights",
    gradient: "from-blue-500 to-cyan-500",
    image: new URL("@/assets/img/tracking-islamic.png", import.meta.url).href,
  },
  {
    icon: BarChart3,
    title: "Data Analytics",
    description:
      "Analytics dashboard delivering deep insights about alumni performance and career progression",
    gradient: "from-purple-500 to-pink-500",
    image: new URL("@/assets/img/analytics-islamic.png", import.meta.url).href,
  },
  {
    icon: TrendingUp,
    title: "Survey Management",
    description:
      "Manage questionnaires and tracer study surveys with ease and efficiency",
    gradient: "from-orange-500 to-red-500",
    image: new URL("@/assets/img/survey-islamic.png", import.meta.url).href,
  },
  {
    icon: Award,
    title: "Comprehensive Reporting",
    description:
      "Generate detailed reports for accreditation and institutional development purposes",
    gradient: "from-green-500 to-emerald-500",
    image: new URL("@/assets/img/analytics-islamic.png", import.meta.url).href,
  },
];

const stats = [
  { number: "1000+", label: "Registered Alumni", icon: Users },
  { number: "95%", label: "Response Rate", icon: TrendingUp },
  { number: "50+", label: "Active Surveys", icon: BarChart3 },
  { number: "100%", label: "Data Accuracy", icon: CheckCircle2 },
];
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 overflow-hidden"
  >
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div
        class="absolute top-20 -left-20 w-96 h-96 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl animate-pulse"
      ></div>
      <div
        class="absolute bottom-20 -right-20 w-96 h-96 bg-gradient-to-r from-purple-400/20 to-pink-400/20 rounded-full blur-3xl animate-pulse"
        style="animation-delay: 1s"
      ></div>
      <div
        class="absolute top-1/2 left-1/2 w-96 h-96 bg-gradient-to-r from-cyan-400/10 to-blue-400/10 rounded-full blur-3xl animate-pulse"
        style="animation-delay: 2s"
      ></div>
    </div>

    <!-- Floating Navbar -->
    <div
      class="fixed top-0 left-0 right-0 z-50 pt-6 px-4 md:px-8 transition-all duration-300"
      :class="{ 'pt-0': isScrolled }"
    >
      <nav
        class="mx-auto transition-all duration-300 rounded-2xl border backdrop-blur-xl"
        :class="[
          isScrolled
            ? 'w-full border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-950/90 shadow-lg'
            : 'w-[90%] border-slate-200/50 dark:border-slate-800/50 bg-white/70 dark:bg-slate-950/70 shadow-xl shadow-blue-500/10',
        ]"
      >
        <div class="px-6 py-4">
          <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
              <div
                class="p-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl"
              >
                <GraduationCap class="h-6 w-6 text-white" />
              </div>
              <div class="hidden md:block">
                <h1
                  class="text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                >
                  Tracer Study
                </h1>
                <p class="text-xs text-muted-foreground">UII Dalwa</p>
              </div>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6">
              <router-link
                to="/public/questionnaires"
                class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Questionnaires
              </router-link>
              <a
                href="#features"
                class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Features
              </a>
              <a
                href="#about"
                class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                About
              </a>
              <a
                href="#contact"
                class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Contact
              </a>
            </div>

            <!-- CTA Buttons -->
            <div class="hidden md:flex items-center space-x-3">
              <Button variant="ghost" size="sm" @click="router.push('/check')">
                Login
              </Button>
              <Button
                size="sm"
                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-md"
                @click="router.push('/check')"
              >
                <Sparkles class="mr-2 h-4 w-4" />
                Get Started
              </Button>
            </div>

            <!-- Mobile Menu Button -->
            <button
              class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              @click="mobileMenuOpen = !mobileMenuOpen"
            >
              <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
              <X v-else class="h-6 w-6" />
            </button>
          </div>

          <!-- Mobile Menu -->
          <div
            v-if="mobileMenuOpen"
            class="md:hidden mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3"
          >
            <router-link
              to="/public/questionnaires"
              class="block py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              Questionnaires
            </router-link>
            <a
              href="#features"
              class="block py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              Features
            </a>
            <a
              href="#about"
              class="block py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              About
            </a>
            <a
              href="#contact"
              class="block py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
            >
              Contact
            </a>
            <div class="pt-3 space-y-2">
              <Button
                variant="outline"
                class="w-full justify-center"
                size="sm"
                @click="router.push('/check')"
              >
                Login
              </Button>
              <Button
                class="w-full justify-center bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
                size="sm"
                @click="router.push('/check')"
              >
                <Sparkles class="mr-2 h-4 w-4" />
                Get Started
              </Button>
            </div>
          </div>
        </div>
      </nav>
    </div>

    <!-- Hero Section -->
    <section class="relative z-10 pt-32 md:pt-40 pb-20 md:pb-32">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <!-- Left Content -->
          <div class="space-y-8" :class="{ 'animate-fade-in-up': isVisible }">
            <div
              class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600/10 to-purple-600/10 border border-blue-200 dark:border-blue-800 rounded-full px-4 py-2"
            >
              <Sparkles class="h-4 w-4 text-blue-600 dark:text-blue-400" />
              <span
                class="text-sm font-medium text-blue-600 dark:text-blue-400"
              >
                Integrated Tracer Study Platform
              </span>
            </div>

            <h1 class="text-4xl md:text-6xl font-bold leading-tight">
              <span
                class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent animate-gradient"
              >
                Track Alumni Success
              </span>
              <br />
              <span class="text-slate-900 dark:text-white"
                >For Better Future</span
              >
            </h1>

            <p
              class="text-lg md:text-xl text-slate-600 dark:text-slate-400 leading-relaxed"
            >
              Advanced platform for Universitas Islam Internasional Darullughah
              Wadda'wah to manage tracer studies, collect alumni data, and
              generate meaningful insights for institutional excellence.
            </p>

            <div class="flex flex-wrap gap-4">
              <Button
                size="lg"
                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-xl shadow-blue-500/50 hover:shadow-2xl hover:shadow-blue-500/50 transition-all duration-300"
                @click="router.push('/check')"
              >
                <Target class="mr-2 h-5 w-5" />
                Start Now
                <ArrowRight class="ml-2 h-5 w-5" />
              </Button>
              <Button
                size="lg"
                variant="outline"
                class="border-2 hover:bg-slate-100 dark:hover:bg-slate-800"
              >
                Learn More
              </Button>
            </div>
          </div>

          <!-- Right Image -->
          <div
            class="relative"
            :class="{ 'animate-fade-in-up': isVisible }"
            style="animation-delay: 0.2s"
          >
            <div
              class="relative rounded-3xl overflow-hidden shadow-2xl shadow-blue-500/20"
            >
              <img
                :src="heroImage"
                alt="Islamic Alumni Success Illustration"
                class="w-full h-auto"
              />
              <div
                class="absolute inset-0 bg-gradient-to-t from-blue-600/20 to-transparent"
              ></div>
            </div>
            <!-- Floating elements -->
            <div
              class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl opacity-20 blur-2xl animate-pulse"
            ></div>
            <div
              class="absolute -bottom-4 -left-4 w-32 h-32 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl opacity-20 blur-2xl animate-pulse"
              style="animation-delay: 1s"
            ></div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          <div
            v-for="(stat, index) in stats"
            :key="index"
            class="group relative p-4 md:p-6 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200 dark:border-slate-800 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-500 hover:-translate-y-2"
            :style="{ animationDelay: `${0.4 + index * 0.1}s` }"
            :class="{ 'animate-fade-in-up': isVisible }"
          >
            <div
              class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"
            ></div>
            <div class="relative">
              <component
                :is="stat.icon"
                class="h-6 w-6 md:h-8 md:w-8 mb-2 md:mb-3 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-300"
              />
              <div
                class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
              >
                {{ stat.number }}
              </div>
              <div
                class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1"
              >
                {{ stat.label }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section
      id="features"
      class="relative z-10 py-20 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl"
    >
      <div class="container mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-3xl md:text-5xl font-bold mb-4">
            <span
              class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
            >
              Key Features
            </span>
          </h2>
          <p class="text-base md:text-lg text-slate-600 dark:text-slate-400">
            Complete solutions for effective and efficient tracer study
            management
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="(feature, index) in features"
            :key="index"
            class="group relative rounded-3xl bg-gradient-to-br from-white to-slate-50 dark:from-slate-900 dark:to-slate-800 border border-slate-200 dark:border-slate-700 hover:shadow-2xl transition-all duration-500 hover:-translate-y-3 overflow-hidden"
          >
            <!-- Feature Image -->
            <div class="relative h-48 overflow-hidden">
              <img
                :src="feature.image"
                :alt="feature.title"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              />
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"
              ></div>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8">
              <div
                class="relative mb-6 w-16 h-16 rounded-2xl bg-gradient-to-br flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500"
                :class="feature.gradient"
              >
                <component :is="feature.icon" class="h-8 w-8 text-white" />
              </div>

              <h3
                class="relative text-xl font-bold mb-3 text-slate-900 dark:text-white"
              >
                {{ feature.title }}
              </h3>
              <p
                class="relative text-slate-600 dark:text-slate-400 leading-relaxed"
              >
                {{ feature.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="relative z-10 py-20">
      <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto text-center space-y-6">
          <h2 class="text-3xl md:text-4xl font-bold">
            <span
              class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
            >
              About UII Dalwa
            </span>
          </h2>
          <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            Universitas Islam Internasional Darullughah Wadda'wah (UII Dalwa) is
            dedicated to excellence in Islamic education and research. Our
            tracer study system helps us maintain strong connections with our
            alumni and continuously improve our educational programs based on
            real-world outcomes.
          </p>
          <p class="text-base text-slate-600 dark:text-slate-400">
            Through this platform, we track our graduates' career progression,
            gather valuable feedback, and ensure that our curriculum remains
            relevant to the needs of the modern Islamic world.
          </p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="relative z-10 py-20">
      <div class="container mx-auto px-6">
        <div
          class="relative max-w-4xl mx-auto p-8 md:p-16 rounded-3xl bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden"
        >
          <!-- Animated background patterns -->
          <div class="absolute inset-0 opacity-20">
            <div
              class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full blur-3xl animate-pulse"
            ></div>
            <div
              class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl animate-pulse"
              style="animation-delay: 1s"
            ></div>
          </div>

          <div class="relative text-center text-white space-y-6">
            <h2 class="text-3xl md:text-5xl font-bold">
              Ready to Get Started?
            </h2>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto">
              Join leading institutions that have been using our platform for
              better tracer studies and alumni engagement.
            </p>
            <div class="flex flex-wrap justify-center gap-4 pt-4">
              <Button
                size="lg"
                class="bg-white text-blue-600 hover:bg-blue-50 shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105"
                @click="router.push('/check')"
              >
                <GraduationCap class="mr-2 h-5 w-5" />
                Access Platform
                <ArrowRight class="ml-2 h-5 w-5" />
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer
      id="contact"
      class="relative z-10 border-t border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-950/70 backdrop-blur-xl"
    >
      <div class="container mx-auto px-6 py-12">
        <div class="grid md:grid-cols-3 gap-8">
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div
                class="p-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl"
              >
                <GraduationCap class="h-5 w-5 text-white" />
              </div>
              <div>
                <h3
                  class="font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
                >
                  Tracer Study
                </h3>
                <p class="text-xs text-muted-foreground">UII Dalwa</p>
              </div>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Modern platform to manage tracer studies and monitor alumni
              success for institutional excellence.
            </p>
          </div>
          <div>
            <h4 class="font-semibold mb-4 text-slate-900 dark:text-white">
              Contact
            </h4>
            <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
              <p>Universitas Islam Internasional</p>
              <p>Darullughah Wadda'wah</p>
              <p>Raci, Bangil, Indonesia</p>
            </div>
          </div>
          <div>
            <h4 class="font-semibold mb-4 text-slate-900 dark:text-white">
              Quick Links
            </h4>
            <div class="space-y-2 text-sm">
              <div>
                <a
                  href="#about"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  About Us
                </a>
              </div>
              <div>
                <a
                  href="#contact"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                >
                  Contact
                </a>
              </div>
              <div>
                <a
                  href="#"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  @click.prevent="router.push('/check')"
                >
                  Login
                </a>
              </div>
            </div>
          </div>
        </div>
        <div
          class="border-t border-slate-200 dark:border-slate-800 mt-8 pt-8 text-center text-sm text-slate-600 dark:text-slate-400"
        >
          <p>&copy; 2025 Tracer Study UII Dalwa. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

@keyframes gradient {
  0%,
  100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.animate-gradient {
  background-size: 200% auto;
  animation: gradient 3s ease infinite;
}
</style>
