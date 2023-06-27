<template>
  <div class="row justify-content-center my-5">
    <div class="col-md-12">
      <div class="card shadow bg-light">
        <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
          <div class="mx-3 my-3">

            <h3 class="h3 my-4">
              Welcome to AI stock images!
            </h3>

            <div class="text-muted">
              <div class="mb-3">
                  <div class="row mb-3">
                    <div class="col">
                      <input type="text" class="form-control" v-model="form.prompt" placeholder="Enter your prompt...">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col">
                      <select class="form-select" aria-label="Select style preset" v-model="form.style">
                        <option value="3d-model">3D Model</option>
                        <option value="analog-film">Analog Film</option>
                        <option value="anime">Anime</option>
                        <option value="cinematic">Cinematic</option>
                        <option value="comic-book">Comic Book</option>
                        <option value="digital-art">Digital Art</option>
                        <option value="enhance">Enhance</option>
                        <option value="fantasy-art">Fantasy Art</option>
                        <option value="isometric">Isometric</option>
                        <option value="line-art">Line Art</option>
                        <option value="low-poly">Low Poly</option>
                        <option value="modeling-compound">Modeling Compound</option>
                        <option value="neon-punk">Neon Punk</option>
                        <option value="origami">Origami</option>
                        <option value="photographic">Photographic</option>
                        <option value="pixel-art">Pixel Art</option>
                        <option value="tile-texture">Tile Texture</option>
                      </select>
                    </div>
                    <div class="col">
                      <label for="customRange3" class="form-label">Resolution</label>
                      <input type="range" class="form-range" min="0" max="2" step="1" id="resolution" v-model="form.resolution">
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary text-white" @click.prevent="generate">Generate</button>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { defineComponent } from 'vue'
  import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue'
  import { useForm } from '@inertiajs/vue3'

  export default defineComponent({
    components: {
      JetApplicationLogo,
    },
    data() {
      return {
        resolution: [
            {id: 1, width: 896, height: 512},
            {id: 2, width: 512, height: 512},
            {id: 3, width: 512, height: 896},
        ],
        form: useForm({
          prompt: '',
          style: '3d-model',
          width: '',
          height: ''
        })
      }
    },
    methods: {
      generate() {
        this.form.post('/generate')
      }
    }
  })
</script>
