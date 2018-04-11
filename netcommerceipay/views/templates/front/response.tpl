{extends file=$layout}
{block name='content'}
    <section id="content">
        <div class="row">
            <h3>{$message|escape:'htmlall':'UTF-8'}</h3>
        </div>
    </section>
{/block}