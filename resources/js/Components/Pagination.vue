<template>
	<div v-if="links.length > 1">
		<div class="d-flex justify-content-center pagination">
			<template v-for="(link, p) in links" :key="p">
				<div v-if="link.url === null" v-html="link.label" class="" />
				<Link v-else :href="link.url" v-html="link.label" :class="{ 'active': link.active }">
				</Link>
			</template>
		</div>
	</div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
	props: {
		links: Array
	},
	components: {
		Link
	}
}
</script>