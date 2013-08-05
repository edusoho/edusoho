<div class="{{classPrefix}}">
    <ul class="{{classPrefix}}-content" data-role="content">
        {{#each select}}
        <li data-role="item" class="{{../classPrefix}}-item" data-value="{{value}}" data-defaultSelected="{{defaultSelected}}" data-selected="{{selected}}">{{text}}</li>
        {{/each}}
    </ul>
</div>
