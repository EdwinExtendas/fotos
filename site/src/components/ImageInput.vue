<template>
    <div class="">
        <div class="image-input-container" :style="{width: width+'px', height: height+'px'}" :class="{empty: !image && !value}">
            <label style="margin: 0;">
                <div class="image-input" :style="{ backgroundImage: 'url('+(image ? image : image_base+value)+')', width: (width-4)+'px', height: (height-4)+'px' }"></div>
                <b-form-file :required="!value&&required" id="banner_image" :plain="true" @change="doUpload" class="hidden"></b-form-file>
            </label>
            <b-progress v-if="progress > 0" :striped=true height="20px" :style="{marginTop: -(6 + height/2 + 20/2)+'px'}" class="image-input-progress progress-xs" variant="info" :value="progress"></b-progress>
            <!--<div v-if="progress == 0" class="btn btn-info" style="width: 100%; text-align: center;" :style="{marginTop: -(6 + height/2 + 20/2)+'px'}">Change</div>-->
        </div>
    </div>
</template>

<script>
import Config from '../../api-config';

export default {
    props: ['value', 'label', 'width', 'height', 'required'],
    data: function () {
        return {
            nr: 0,
            progress: 0,
            image: null,
            image_base: Config.base_image_url,
        }
    },
    methods: {
        doUpload (event) {
            const file = event.target.files[0];
            const fixedWidth = event.target.getAttribute('fixed-width'),
                fixedHeight = event.target.getAttribute('fixed-height');
            const _URL = window.URL || window.webkitURL;

            const component = this;

            // check image size
            let img = new Image();

            img.onload = function() {
                // here you got the width and height
                if (this.width !== fixedWidth
                    || this.height !== fixedHeight)
                {
                    // TODO do we want this? If yes, implement proper error message
                    // throw Error('not the right size');
                }

                const formData = new FormData();
                formData.append('file', file, file.name);

                component.$http.post('/api/image/upload', formData, {
                    progress(e) {
                        if (e.lengthComputable) {
                            component.progress = e.loaded / e.total * 100;
                            // component.$refs.progressbar.value = function () { return e.loaded / e.total * 100 };
                        }
                    },
                }).then(
                    (response) => {
                        component.$emit('input', response.body.file);
                        // component.value = ;
                        component.progress = 0;
                    },
                    () => {
                        // handle error
                    }
                );
            };

            img.onerror = function() {
                // TODO add error for incorrect file
                // throw Error('incorrect file type. Given file is not an image');
            };

            img.src = _URL.createObjectURL(file);
            component.image = img.src;
        }
    }
}
</script>
