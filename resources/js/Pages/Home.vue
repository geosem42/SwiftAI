<template>
	<app-layout title="Home">
		<main>
			<div class="container-fluid px-4">
			<h1 class="mt-4">Swift AI</h1>
			<div class="card mb-4">
				<div class="card-body">
					<p>Swift AI is a powerful web application built using Laravel, Inertia.js, and Vue.js. It serves as a centralized hub that seamlessly integrates various AI tools and services from companies such as OpenAI, Microsoft, and Stability AI. The platform aims to streamline the usage of diverse AI solutions, providing users with a cohesive experience.</p>
					<p>Created by: <a href="https://github.com/geosem42">George Semaan</a></p>
					<p>Github: <a href="https://github.com/geosem42/Swift-AI">https://github.com/geosem42/Swift-AI</a></p>
				</div>
			</div>
		</div>
		</main>
	</app-layout>
</template>

<script>
import { defineComponent } from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"

export default defineComponent({
	components: {
		AppLayout
	},
});
</script>