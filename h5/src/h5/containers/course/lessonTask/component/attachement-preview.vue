<template>
  <div style="margin-top: 8px;">
    <div v-if="disableVideo && isMedia" class="attachment-preview">
      <img :src="disableVideoIcon" style="width: 24px;margin-right: 8px;" />
      <div class="text-overflow text-12" style="flex: 1;color: #999;">
        <template v-if="storageSetting.securityVideoPlayer && !this.isWechat()">
          {{ $t('attachment.disableVideo') }}
        </template>
        <template v-else>
          {{ $t('attachment.disableVideo2') }}
        </template>
      </div>
    </div>
    
    <div v-else-if="!isMedia || (isMedia && !isLoaded)" class="attachment-preview">
      <img :src="iconSrc" class="attachment-preview__icon" />
      
      <div class="text-overflow" style="flex: 1;">{{ attachment.file_name }}</div>
      
      <div v-if="['other', 'audio'].indexOf(attachment.file_type) === -1 && attachment.convert_status !== 'success'"  class="attachment-preview__status">
        {{ resourceStatus[attachment.convert_status] }}
      </div>

      <div v-if="attachment.file_type == 'other'" class="attachment-preview__status">
        {{ resourceStatus.other }}
      </div>

      <img
        v-if="(attachment.file_type !== 'other' && attachment.convert_status === 'success') || attachment.file_type === 'audio'"
        width="30"
        height="30"
        style="margin-left: 8px;"
        @click="previewFile"
        :src="icons.preview"
        class="review-img"
      />
    </div>


    <div v-if="isMedia" :id="playerId"></div>

    <van-popup
      v-if="!isMedia"
      v-model="isShow"
      closeable
      :close-on-click-overlay="true"
      close-icon="close"
      position="bottom"
      get-container="body"
      :style="{ height: '70%' }"
    >
      <div style="height: 52px;display: flex;justify-content: center;align-items: center;color: #272E3B;">
        <div class="text-overflow" style="width: 300px;text-align: center;">{{ attachment.file_name }}</div>
      </div>
      <div :id="playerId" style="width: 100%;height: calc(100% - 52px)"></div>
    </van-popup>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';

const icons = {
  audio:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAS1BMVEUAAABgu2Bgul9gul+f35+a5Jhgul9guWBgul5gu15guWBgul9gul5gumBgu15gul9gumBgu1+I2IZgul6Z5JeL2YmR3o95zHdxx28P/4dOAAAAE3RSTlMAQJ+/EM/fIJDvEM+AMHBgUK/PwGlb5wAAAjlJREFUeNrt3N1u2zAMhmFK2vTrn2RM2t7/lY7F1gRdZxirxPgrxvfcwgNbTnwgkCzLsixLubr4WPjT/fhGqiVfuKvrd1XhxL1dL4rC1HgAUE+YIo8Aqglr5CFANWHmMUAt4cyDgFrCMBQoQswbKMCbEHEH3oHDhW0YUEdYeTxQhHhbUIA6QqcCFCE4UITgQBGCA0UIDhQhOFCE4EARggNFCA4UIThQhOBAEYIDRQgOFCE4UITgQBGCA0UIDhQhOFCERwOfL3vCg4F82RUeDLzuC48F8tO+8Fjgy2VfeCiQn8cK70DopzwWyC/Xp8FCxw/OGdCABjTgu74usERoYHRE1S05oAITvTWfcihwwEDvS24KUEBPH0tncCBRRgdSQAc6dCAVdGBDBwZ0INwjdusp0b0J7SXJLMXz4iq9tqD9Dq58q/kpB7h/koD7uWVAAxrQgAY0oAENaEADGtCABjTg/wzM6MAVHBgTNrCcCBIYf5dnwgOW7CrdwgPmVx0wcCWCBi6EDfQEDkzgwEBbRQzgRFuBnFnYBDoQoKeNPAgw0kYRBLi15ARzbqb9/UKgk0eePrYWICC39OdVAe3slp/prXrKEfFwWQleCq18ldNvBjSgAX9lQAMacGc9AxrQgDvrHQ5M/ODmvpFC+lX6xyI/tNY31ko/T4T9lsxdo9X0C0TYt3AmIuRdmOkz1cYPKtahIybH+1LPkE79Wuoac6re1DsoljUrPlFvaT23wgqV6JdKlmVZlqXbT5+0CuolQf16AAAAAElFTkSuQmCC",
  video:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAATlBMVEUAAACLbNOMa9SIateLatTPv+/LvOuMatSMcNyLatSLa9SMatSMatSKatWLatSLadSKatWMatS4pOSLatTLvOu7p+Wjid2SctfBr+izneK03+pEAAAAE3RSTlMAQL8ggBDP3xCQz5/vMO9wYK/PFAxd6QAAAiVJREFUeNrt3N2K2zAQhuGxxq0ky85Px0l27/9GOwtlaUtYJY5GHtjvPQzk48EoJ8E2IYQQQsalY4mzbO7XDzKNyywvdflpKhzl1S6roZAnaQC0E3KUFkAzYYrSBGgmzNIGaCVcpBHQShiaAlXo8wIq8FPo8QR+AI2EUzOgjTBJe6AK/R1BBdoIBxOgCp0DVegcqELnQBU6B6rQOVCFzoEqdA5UoXOgCp0DVegcqELnQBU6B6rQOVCFzoEqdA5UoXOgCncHvq8V4d7A61oT7gy8rVXhvkC51IX7Am9rXbgrUK4mwkGcC1sC5fZWF+4J1K7vb42Fg3RuABBAAAH8JwABBLCyByCAAFb2AAQQQPmikMevy0Hudi49gHHYeFvVPOiuPTAy0SbhxB8f2wNPGxcyURfggR7tIH81n6gTMGy68yYy+QMG+Swn8gycj0SegXEh18DI5BvI5BsYyDkweweOAAIIIIDfHFi8Aw/JOVCyd6CM3oFSknOgRHYOlMOxHzBue+AjdwNKokr3KZF7ATM9VpH/hKdOQBnpkcb7X1w6AKVUx9IQ5E4TpzP+Yf0TgAACWNkDEEAAK3sAAghgZQ9AAAGs7AEIIICVPQAB/PZAls4t9FxJOpfoyaJ0baJny9K1QuT7V7LQ0wXpWCDyfQkXIvJ8CjNtKU3SqZgMXjHZ0sd7vqSz3sS0vVHMG+mluIhlc2F6NT6dp1kMmmM5JkIIIYRs+w0UNMmnBMQlQgAAAABJRU5ErkJggg==",
  ppt:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAUVBMVEUAAADfYDTfYDDeXzHvr6/wtqPfYDDeYDHfYDDeXzHeXzHfXzHfXzHdYDHfYDHeYDHeXzDeYDHeXzLrnIDeXzHwt6LroYbha0DlhGDtqpHplnhmzG3pAAAAFHRSTlMAQEDfEM8ggBDvz7+fYDCQcFCvz/A/iYQAAAJdSURBVHja7dzhbpswFIbhU7wZ29iQdIek7f1f6M6PjdCtiUWx8Vf1vH8tRY8QCmBbJk3TNE2rnDtF4/nT/fpBVbPR864uP6sKe97bZa4otIELAOsJreESwGpCZ7gIsJpw4jLAWsKRCwFrCYeiQBFiXkABLkLEO/AGLC4MxYB1hI7LA0WIdwsKsI7wqQpQhOBAEYIDRQgOFCE4UITgQBGCA0UIDhQhOFCE4EARggNFCA4UIThQhOBAEYIDRQgOFCE4UITNgW9zRtga+DLnhI2B1zkrbAvkS17YFnid88KmQH6pIRQguLAkkK+veWFToBDfXssKBXhwnQIVqEAFvuvLAIez/b/uPA0gwJ7uZZMBAEZ6VDLNgWNuub4x0FOuU1ugoWwJHUgJHUg9OpAGdGAHATSrwnOidREB+O/ImtgBAt8/BAdEIJ9oqYcEsl0GR0zgREuYQE9LBhK4etcZMIGdAncCHThwNeohgWkZdJB/M6vBMyLQW1qKgEAz0i0DB/S9o1sJ4XWrX5U6R+sMAvBBJ4hX/vtZjw10BvyzM4J/uEduCWTKZANDA5NnZODT0H6Gle429gFhjvrjJ8kUgweZ5acl0GUIBSpQgQpUoAIVqEAFKlCBRYAOFLj86hkUONCfDCjw76rwhLu5LKTRpgC8+w1+e54CFajAOylQgQrkxylQgd8eaPngRtqW44NztDHDhxZoaxMfWqStdXxoI21u4AMbiLAv4UhEyHfhRJ/JBT4o44oeMVneZ1se0pkv2JbHnObraVc2cs18tLQ3m56D5wp5E0+ONE3TNK1uvwF3ch6uj7dPzAAAAABJRU5ErkJggg==",
  pptx:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAUVBMVEUAAADfYDTfYDDeXzHvr6/wtqPfYDDeYDHfYDDeXzHeXzHfXzHfXzHdYDHfYDHeYDHeXzDeYDHeXzLrnIDeXzHwt6LroYbha0DlhGDtqpHplnhmzG3pAAAAFHRSTlMAQEDfEM8ggBDvz7+fYDCQcFCvz/A/iYQAAAJdSURBVHja7dzhbpswFIbhU7wZ29iQdIek7f1f6M6PjdCtiUWx8Vf1vH8tRY8QCmBbJk3TNE2rnDtF4/nT/fpBVbPR864uP6sKe97bZa4otIELAOsJreESwGpCZ7gIsJpw4jLAWsKRCwFrCYeiQBFiXkABLkLEO/AGLC4MxYB1hI7LA0WIdwsKsI7wqQpQhOBAEYIDRQgOFCE4UITgQBGCA0UIDhQhOFCE4EARggNFCA4UIThQhOBAEYIDRQgOFCE4UITNgW9zRtga+DLnhI2B1zkrbAvkS17YFnid88KmQH6pIRQguLAkkK+veWFToBDfXssKBXhwnQIVqEAFvuvLAIez/b/uPA0gwJ7uZZMBAEZ6VDLNgWNuub4x0FOuU1ugoWwJHUgJHUg9OpAGdGAHATSrwnOidREB+O/ImtgBAt8/BAdEIJ9oqYcEsl0GR0zgREuYQE9LBhK4etcZMIGdAncCHThwNeohgWkZdJB/M6vBMyLQW1qKgEAz0i0DB/S9o1sJ4XWrX5U6R+sMAvBBJ4hX/vtZjw10BvyzM4J/uEduCWTKZANDA5NnZODT0H6Gle429gFhjvrjJ8kUgweZ5acl0GUIBSpQgQpUoAIVqEAFKlCBRYAOFLj86hkUONCfDCjw76rwhLu5LKTRpgC8+w1+e54CFajAOylQgQrkxylQgd8eaPngRtqW44NztDHDhxZoaxMfWqStdXxoI21u4AMbiLAv4UhEyHfhRJ/JBT4o44oeMVneZ1se0pkv2JbHnObraVc2cs18tLQ3m56D5wp5E0+ONE3TNK1uvwF3ch6uj7dPzAAAAABJRU5ErkJggg==",
  doc:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAUVBMVEUAAABNh+dQh+dPh+apyfRQgN9Ph+ZQh+dOheROhuZOheZPhuZOhuZOhuZOhuZOh+ZSi+pPhuZPh+ZQheSWue9OhuapxvOevvFwnuuFre9ZjuhAHZO1AAAAFXRSTlMAYECfXxC/IJBwgO/vz1DfMN+vMFCo01ueAAADvElEQVR42u3a2XKbMBQGYKGVxSx2KznJ+z9oLzypID8nLjU60mT0X3VMLD5rO3SQqKmpqampqal5PWbq1f/nt0gcaf1LuWuRMnL0L+YeUgp7718HJhQqfwYwnbD1pwCjsLzx/QQmETp/EjAKy5uAERh0oR34Fxh0iSskAs8XjqcB0wiNPw2YRijPA6YRTucB0wibNMCgSwcGXTow6NKBQZcODLp0YNClA4MuHRh06cCgSwcGXTow6NKBQZcODLp0YNClA4MuHRh0fuB7+F5YOjDo3MC38EyYGejvaYSNZ+vCoPMC/ftzYV7gx/0UIQLL7sNTgf7j/f5MyA1E49v3WQ4DmSMrsAIrsAI3qcCfDWylEFL5Axk6I0wzMwGbw29Dr+7x8mVkAarPv7H/DHTiETdwAN3hlz02vkJlAF6+e11mu6bFmdaLz3ScQBzj6+MuLX3znnWIRU9cuZE/6cYB7Kg3jop4GX6LzbIsEkvdbyLGvoFflBRIjxh1YGSBNZIIiG11OJLYU1dYVamAuGs4HEkcewX7UjIgTsIZ13DcaWBqSiagN3uQEV7YA7zlAk57kF6sYoY9+MgFbCOEuoPd+WPnmYC4LuNnuL5lbJMBSFc7RZ2sip8pBiBd7SY4uYRLng+ocMczYpsefssvzwfERy4LR5ewzjECsdo10MIA64kT2H1dDQ5aUF8ng+cD4iPXSLQAj1pcwIvZ9lQrIAbqHA8Qt1+qeQt1jguI1e4qdtJBnWMDQteslsK0Icn4OSMQH7miSm4rx2qqMgJhea61l7V8rWUD4oP8sHKM696EOscDxGoXe9Nt67SE9jiAWGRd/NpG3sZ/3viAWO3Mdu+TexcGfqCljof3AiM9AxCrHWSi5D07EJuMm92O3OYAtgIyx9kJQ88PxKGU1IUpCxCHsqVmp8oCxMf8kbrZnAOIk9DRF/IAr9R3LnAhBxD/p2Spu91yAbvdvQSLyZALeKP2Egu7Dz8Q55qidqAuCxBbnanBtzmAONd+UVXG+BxAdPTUo47MAsS5NlL3a3MCJ6patCDPA2ypL1xAzgtEx426YZMDiGPsyE18zgu8LA8fdQ7AqOwHe5RcZH/xGNssSzfXk0dEKrACK/BJexVYgRX4pL0KrMAKfNJeBT7L5JnjDgKdZ44RBzN41oziaKxnjToM7Dxr5GGgYR3jqzie3jOmEcdjZk+ngA4UYvFcuX5ugoVWk2ERjxS6ksF3IAvDPBydeCGmT0wcOvFiXJOupgy2MeKMLLJJEOlETU3NT88fCCsl2s8Xx2oAAAAASUVORK5CYII=",
  docx:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAUVBMVEUAAABNh+dQh+dPh+apyfRQgN9Ph+ZQh+dOheROhuZOheZPhuZOhuZOhuZOhuZOh+ZSi+pPhuZPh+ZQheSWue9OhuapxvOevvFwnuuFre9ZjuhAHZO1AAAAFXRSTlMAYECfXxC/IJBwgO/vz1DfMN+vMFCo01ueAAADvElEQVR42u3a2XKbMBQGYKGVxSx2KznJ+z9oLzypID8nLjU60mT0X3VMLD5rO3SQqKmpqampqal5PWbq1f/nt0gcaf1LuWuRMnL0L+YeUgp7718HJhQqfwYwnbD1pwCjsLzx/QQmETp/EjAKy5uAERh0oR34Fxh0iSskAs8XjqcB0wiNPw2YRijPA6YRTucB0wibNMCgSwcGXTow6NKBQZcODLp0YNClA4MuHRh06cCgSwcGXTow6NKBQZcODLp0YNClA4MuHRh0fuB7+F5YOjDo3MC38EyYGejvaYSNZ+vCoPMC/ftzYV7gx/0UIQLL7sNTgf7j/f5MyA1E49v3WQ4DmSMrsAIrsAI3qcCfDWylEFL5Axk6I0wzMwGbw29Dr+7x8mVkAarPv7H/DHTiETdwAN3hlz02vkJlAF6+e11mu6bFmdaLz3ScQBzj6+MuLX3znnWIRU9cuZE/6cYB7Kg3jop4GX6LzbIsEkvdbyLGvoFflBRIjxh1YGSBNZIIiG11OJLYU1dYVamAuGs4HEkcewX7UjIgTsIZ13DcaWBqSiagN3uQEV7YA7zlAk57kF6sYoY9+MgFbCOEuoPd+WPnmYC4LuNnuL5lbJMBSFc7RZ2sip8pBiBd7SY4uYRLng+ocMczYpsefssvzwfERy4LR5ewzjECsdo10MIA64kT2H1dDQ5aUF8ng+cD4iPXSLQAj1pcwIvZ9lQrIAbqHA8Qt1+qeQt1jguI1e4qdtJBnWMDQteslsK0Icn4OSMQH7miSm4rx2qqMgJhea61l7V8rWUD4oP8sHKM696EOscDxGoXe9Nt67SE9jiAWGRd/NpG3sZ/3viAWO3Mdu+TexcGfqCljof3AiM9AxCrHWSi5D07EJuMm92O3OYAtgIyx9kJQ88PxKGU1IUpCxCHsqVmp8oCxMf8kbrZnAOIk9DRF/IAr9R3LnAhBxD/p2Spu91yAbvdvQSLyZALeKP2Egu7Dz8Q55qidqAuCxBbnanBtzmAONd+UVXG+BxAdPTUo47MAsS5NlL3a3MCJ6patCDPA2ypL1xAzgtEx426YZMDiGPsyE18zgu8LA8fdQ7AqOwHe5RcZH/xGNssSzfXk0dEKrACK/BJexVYgRX4pL0KrMAKfNJeBT7L5JnjDgKdZ44RBzN41oziaKxnjToM7Dxr5GGgYR3jqzie3jOmEcdjZk+ngA4UYvFcuX5ugoVWk2ERjxS6ksF3IAvDPBydeCGmT0wcOvFiXJOupgy2MeKMLLJJEOlETU3NT88fCCsl2s8Xx2oAAAAASUVORK5CYII=",
  pdf:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAATlBMVEUAAADfYDLfYDDvr6/wtqPeXzHfYDDdYDDeXzHeXzHfXzHfXzLeYDHeXzHeYDHeYDLfYDDfYDLeYDHrnIDeXzHwt6LsooflhGDhbkPplnjsKT+gAAAAFHRSTlMAQCAQz98QYO+Av59wz1CQMK/fzwUGIJMAAAOaSURBVHja7NbBkoMgEARQZFcEEU1qR5P8/4/urJPokaoNE/pAX/TS5StEHNPS0tLS0qIcN8Xe07/z82VUY6Ont7J9qwoDvZttVRTaRAWAekLbUwmgmtD1VASoJlyoDFBLeKFCQC3hWA4oQswFFKAIEXegAFWEqRhQR+ioMFCEeFuQgTrCTgXIQnAgC8GBLAQHshAcyEJwIAvBgSwEB7IQHMhCcCALwYEsBAeyEBzIQnAgC8GBLAQHshAcyMLqwMeaEdYG3tacsDKQ1qywMnDLC+sC72teWBVINxVhR+DCkkC6b3lhPaAQH1thYUcfTteADdiADZiLjyFce1ign6QYQIHevpoXTOBwVgMi0JszzgMCR2mlyXAWQOAiJVnJARAY9tJMZPlyAQQO5rl0M18sMvDvxgECg5SeUkDg8jqjB9BXHI0R2Qz6kSRp+f05HSDQSyuRAz0HSWaF6FH/JDTvrSkazogIlGnQWcNBHBboelYt4rhF/qzOkECyRzViAqejmjCB8aiOmMDfds5tN3IQBqDEkjH3XMT/f+tq1iGlg7artBrXVTlPjDRMjpzByIlFfpuqU5B4nto8iOZE6V5cvekICgWBm9Z5Mjh1gryGyVmendQJwnlrV8OsygQD39nuOkGX4HLtcQg83+6aBLFbGsGcrIoEW1XcCjyG8PHRHccRjsOJC45JGp9TItAGpmGXFMQFxwD2hiOA3yKIw7pdzb+I3yFIw4S2lkeskxfEITZhLdZ0QKfr5QXpXaXkAr2TW847jyH91SzSgi2AnJebXQ+FvioAcUG6Kjnn2W4AUmZ9QcExB4YPrwDFx/TwX4QFXeEAPgUPKGYkY0airGAEM2BLdGd0CcZULSkYltHuaT+LT1/ZBRM1lv/YMRjfkqLdq5zgypfdymA3kmMqy0LRVTFBd3rR3vKxrhfaCG3jOgeo6417ts0vnQNdLQHNL7bHlqCrZwGh+bUR6hJcrqJo45HX1fURL6vEI1DWlsJhK1fpAahLEDktY/Mzu7LGHi7M0+XntXUecQRpaX76WqOs6fAKe7d876eyuez6KRt0dr85Yj3v1LbnZU8puh/XPzgFp+AU7JmCU3AK1o+ZglPw1wtCFWYz97BVGGtuglWULH6s1f3WL92rZJM/Wu1u24juED4CqPlfuJrPYHMVAq3kEZP3QZA8pPM+GSSPOb2PN18CYn0lLoL5KkB7dvUFuCMmayaTyWQyeS1/AKtBL8WRjteoAAAAAElFTkSuQmCC",
  xls:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAS1BMVEUAAABIpWBIo2BIpF9IomBIo1+u17dJo19Io19Jo19QqGBIpF9Jo2BMo2JJpF9Jo2BJo1+ZzKZIo1+s1bdttYCfz6yGwpZernJ6vIu659ZwAAAAEnRSTlMAYECfIN9f74CPEL9wMM9Qr1BwWUG9AAADG0lEQVR42u3c23KbMBCA4QV0QhycrnLo+z9pO5k2iypblBqxO5397zJM7G+MkC3kBDRN0zRN054v3sLw732DxvUTPtWbg5b1Kz7ZW2opDIjPAxsKBzwD2E444jlAEoo7v5/AVkKDZwFJKG4AEjA5oS/gFzA5iVcIAc8XrqcB2wgjngZsI+zPA7YR3s4DthF2bYDJSQcmJx2YnHRgctKByUkHJicdmJx0YHLSgclJByYnHZicdGBy0oHJSQcmJx2YnHRgcvzA91QXSgcmxw38SHtCZiCmXSEz8HVfyAvE930hLxDf9oW8QPzeQEjAa06zYwJSH6/1Fl7gfr0CFahABWYpUIEKxL9q6o3pLD7Ih8UsnWUEhuq+njWfR41lAw7wq6n6nIYNuMBvgseyAORnAlZ3zlY6GpiAvra9bA0/EDeGuGJeB9QLF3AEyvjyEPtFkj3qjJssbJr4gDYCNWQnnwqs7yRARZqQZ6AWZAKWlJ4mcMpYXqBfymFoTXbieYFoY3E5ZGb+j1vFXBOyGYYfiF0+DCfYZCUAvQFqtNlP7B9Yi7kGeqA6/k/Um2FYZqwU4INHXwWsSWiuKQsSFk00DIt6Eau6r+ZyAMoC4gJ5g4x18cNhOAtZuD+aa4yUOwsPp2t5QGtg2ygO2EHeKgwYimnGiwJaKJolAb2BslEQcIY7RSsGOMDdFinAbIaJQM1CgFtfyFd5IoB/rOJGoKKERdOUg/IrpucH5qu4cpXHDryVv7TCppUPWA5AW7mxyQS0sMnefbaOC1gMt/DoZgMXsHLB5pe2gJ2mbBVX0vl3mn72Uh6TtNMEoVzl8W/k+M0Jrp7/mX2nKdrqFTRwAQPNJLU5yHgeIC3lQv1t+oXxnWQ0AMuID7Id/aMBJiCi91jLbg7rtz62KVCBCsR6ClSgArGeAhWoQKynQAW2Bt7w4gwcy+DFRTiYx0tb4WgTXtoAR5vx0no4WvR4YRaOF/DCOqh3eLtXwAsI0GPbyvt07H/Cu/dNYqFXct1Xb7HYvNXAE8XQmOgDPJnpJmyVn+YIZ7T0XYNuBjRN+9/7AbrEwBGtP7rRAAAAAElFTkSuQmCC",
  xlsx:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAS1BMVEUAAABIpWBIo2BIpF9IomBIo1+u17dJo19Io19Jo19QqGBIpF9Jo2BMo2JJpF9Jo2BJo1+ZzKZIo1+s1bdttYCfz6yGwpZernJ6vIu659ZwAAAAEnRSTlMAYECfIN9f74CPEL9wMM9Qr1BwWUG9AAADG0lEQVR42u3c23KbMBCA4QV0QhycrnLo+z9pO5k2iypblBqxO5397zJM7G+MkC3kBDRN0zRN054v3sLw732DxvUTPtWbg5b1Kz7ZW2opDIjPAxsKBzwD2E444jlAEoo7v5/AVkKDZwFJKG4AEjA5oS/gFzA5iVcIAc8XrqcB2wgjngZsI+zPA7YR3s4DthF2bYDJSQcmJx2YnHRgctKByUkHJicdmJx0YHLSgclJByYnHZicdGBy0oHJSQcmJx2YnHRgcvzA91QXSgcmxw38SHtCZiCmXSEz8HVfyAvE930hLxDf9oW8QPzeQEjAa06zYwJSH6/1Fl7gfr0CFahABWYpUIEKxL9q6o3pLD7Ih8UsnWUEhuq+njWfR41lAw7wq6n6nIYNuMBvgseyAORnAlZ3zlY6GpiAvra9bA0/EDeGuGJeB9QLF3AEyvjyEPtFkj3qjJssbJr4gDYCNWQnnwqs7yRARZqQZ6AWZAKWlJ4mcMpYXqBfymFoTXbieYFoY3E5ZGb+j1vFXBOyGYYfiF0+DCfYZCUAvQFqtNlP7B9Yi7kGeqA6/k/Um2FYZqwU4INHXwWsSWiuKQsSFk00DIt6Eau6r+ZyAMoC4gJ5g4x18cNhOAtZuD+aa4yUOwsPp2t5QGtg2ygO2EHeKgwYimnGiwJaKJolAb2BslEQcIY7RSsGOMDdFinAbIaJQM1CgFtfyFd5IoB/rOJGoKKERdOUg/IrpucH5qu4cpXHDryVv7TCppUPWA5AW7mxyQS0sMnefbaOC1gMt/DoZgMXsHLB5pe2gJ2mbBVX0vl3mn72Uh6TtNMEoVzl8W/k+M0Jrp7/mX2nKdrqFTRwAQPNJLU5yHgeIC3lQv1t+oXxnWQ0AMuID7Id/aMBJiCi91jLbg7rtz62KVCBCsR6ClSgArGeAhWoQKynQAW2Bt7w4gwcy+DFRTiYx0tb4WgTXtoAR5vx0no4WvR4YRaOF/DCOqh3eLtXwAsI0GPbyvt07H/Cu/dNYqFXct1Xb7HYvNXAE8XQmOgDPJnpJmyVn+YIZ7T0XYNuBjRN+9/7AbrEwBGtP7rRAAAAAElFTkSuQmCC",
  other:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAMAAAC8EZcfAAAAUVBMVEUAAABQh+evz++pxvNQh+dPh+ZOheZQgN9Oh+ZOhuZPh+ZPhuZOheVQielPhuZPhuZOhuZOhuZPiOZOhuaOse9OhuapxvOStvBwnutXjeiHru5GSeLhAAAAFXRSTlMAQBDPIJ+AEN/Pv++QMN9gcFCv788LzAT2AAACc0lEQVR42u3c227bMAyAYXrT2ac4nZy07/+gY26KDVtrxxRpFuX/AMQHRb5wJBgsy7Isi7l48emlHu7XD2DNecRRuv1kFYZK7bYyCl2uDYB8QpdqCyCbMKbaBMgmHGobIJdwrI2AXMLSFIhCnQuIwHehxh34ADIJczMgjzDW9kAU6tuCCOQRdixAFCoHolA5EIXKgShUDkShciAKlQNRqByIQuVAFCoHolA5EIXKgShUDkShciAKlQNRqByIQuVAFJ4OfFsJQgngfSUIJYCvK0EoAaw3glAE+LoShBLAeucQIlC58AFU/St3tWn3t1tbIQKF6wxoQAMa8K8MaEADbswzoAENWJ+vz34IIfiSNQJLGOG92PmkC+j/nT8nPcDy3+lIVAK8wAe5qwZgP8LHhfOBk4PPCmcDe/QRhPzABbYaTgUGwh01CeBEGCUCXGBP/jRggV25nhdIHzqcBJwIw0SAM+ytnAEsXYS9uTlJA72D55qTJLDv4PkGOeDk4EiBFUj3oVAIuMDRriJAD4eLvQTQwfGCANADodjzAxegVPiBkQS8sAMnILWwAwsN6AyYacCRHfiifQ9WRwIGfuCFBMz8wEJ7RviBOOp4XgKYKQsoAazDYV/iBNKfk5jFXpoCwccBpL91Yl0SfS+e5ueInZf/6+M6jxH25LpQ6uknTV/hrM6ABjTgHxnQgAbcmGdAAxpwY54BDWjAjXkGNOC3B7oq3Ej4pBBH9CPAVEXLhDsaInkA3U/JSDh6lqgA6F7CxwJq3oUD4dRAoikSzl0EmhzhI50CZdpFHPYCkHK+ctZ7B9TcfM19ZahP/hLBsizLsnj7DcN6FTzwRZuzAAAAAElFTkSuQmCC",
  preview:
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAMAAAANIilAAAAAclBMVEUAAABYWlpYXFxYWlpZW1tYWlpYW1vl5evn5+tRXV1YXFzm5uzl5uzk5+taWlpZWVlXWlrf3+9YWVlWWVnl5uvl5etZWlrl5utZW1tXWlrk5erq6urk5+paYGBVWlrf39/l5uxZWlrl5evn5+vl5utYWlrPScmYAAAAJHRSTlMAnyDf74C/kCAQQN+fQDBwzxBgUM/Pr++PkIAwMDAwEO+QgH/dkb0JAAABiklEQVRIx+3XyW7DIBQFUB6Tjac4caZm6Pju//9iMSat1I3BWVSRchdBRjrhQQgyYkxb1JyVXVOKKeuCF6RYB7vjw6kVWbk0fvRRF1yXIjtlzYX/ZPZ2gT5wKxpuxKJ8+qG3fF6GW64Fs1gY5id+4ofEbjBEZAaZjyuLW/pNHpYj1T3R3moAqsrAgxe2crEG8twk482fsaTyOhFfvZW+dUapnsJ6EbBKwlJN9qoxRnVRdymYoEcrtZ+2rOKTU+gTsIwVEig87/ESls13z2KPVGhVrPM1Pn9Au1nsR4jtT0f8EnSz2MKGVkPGaejQGqj5kStgmMo/ijj3uBQmYbXttL4dcJTCHQE59aqk31mjd2GfTdlMRWOYxbHw/aglaWjbRWsS97YJ+/M3jgASKThqUHejRgMvOf9n5bnaG2OoB6BXeSfJxvMYbVz2GdatyFpLq+qxjt4nfuL/xzW3y+yFt6Lg0yIb3vJbPiy7JjCX911QxHrL3JzzaPv1zru3Oy5l3oaUzTZP1kU7um+nSVMnXPP4TQAAAABJRU5ErkJggg==",
};


// const playerInitQueue = [];
// const playerList = [];
// let queueLength = 0;
// let i = 0;
// const startInitAllPlayer = () => {
//   if (i > queueLength) return;

//   const currentPlayer = playerInitQueue[i]();

//   playerList.push(currentPlayer);

//   currentPlayer.on("ready", () => {
//     i++;
//     startInitAllPlayer();
//   });

//   currentPlayer.on("playing", () => {
//     playerList.forEach(player => {
//       if (player !== currentPlayer) {
//         player.pause();
//       }
//     });
//   });
// };

const disableVideoIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAAA4CAMAAACfWMssAAA
AOVBMVEUAAADPz8/MzMzMzMzIyMjMzMzLy8vLy8vNzc3MzMzMzMzKysrNzc3MzMzLy8vNzc3MzMzMzMzMzMwPcwYrAAAAEnR
STlMAEM/fIO9wQKC/YDB/kICPUG9Rq5a2AAAB1ElEQVRIx82WW7aDIAxFDe+3tvMf7G1RbogBbf+aL2Gxk0NIwOVXDDazupe
txsLnlF3lszOx2o9iJfFkJsMt99CE+BSNGI2j6iJcW6S92RS8dEdb/D+aJhi4Y4E3QOZD07GOOXFgdpBneSQYBuDOaTOWY/S
MXG8yoPag7jyfmENG7pLMaZZx0yREMilR5wUpq/d+KtS8MI6p1WexEicuzdQAQANKcnBhHL6WUaEB7SnHCQagxV3iqDM9bQjf
x8jvAVlFGoLvMvfFBgRs5s4oaEwH1NJeKNhMp5FWwC0WBmLvc60bfloGovle74ZHnrECGci3qjA7DlVzkOUXqiMElwuQ9uGXY
Mbi+Fwq7RzzHmFy1CWYe78qADsZBvJ7j53MFJRD7L7kCjCAtKOeFDnDestM63xzvK09a2QdujfzCP0QpHDFucwN3Zxrd256J6
tTX1hIG4A4rmTlyJGDvr4eSwXE8bzw+1JdXf7NdORvnIQbknNL3MXcksih2EpO1abpk+yw02ccJ/lTzrk5CWLaDWH3uS/wg5pt
TgMMxOh4uFacLNi7xUakfeWaKJj9WqDJdByWfMrY/qHKMrRA0dg8Avm4R7flG7NZ4AX+pYE1xbmslh+yP6jDPH3+K7RyAAAAAElFTkSuQmCC`

export default {
  name: 'itemBankResourcePreview',
  props: {
    attachment: {
      type: Object,
      require: true
    },
    canLoadPlayer: Boolean
  },
  data() {
    return {
      isLoaded: false,
      isShow: false,
      icons,
      disableVideoIcon,
      resourceStatus: {
        'waiting': this.$t('attachment.waiting'),
        'doing': this.$t('attachment.doing'),
        'error': this.$t('attachment.error'),
        'other': this.$t('attachment.other')
      },
      randomId: Math.floor(Math.random() * 100)
    }
  },
  computed: {
    ...mapState({
      storageSetting: state => state.storageSetting,
      language: state => state.language
    }),
    isMedia() {
      return ['video', 'audio'].indexOf(this.attachment.file_type) > -1
    },
    // isImmediatePreview() {
    //   return !this.disableVideo && this.isMedia && this.attachment.convert_status === 'success';
    // },
    iconSrc() {
      const { ext, file_type } = this.attachment

      if (file_type === 'other') return icons.other

      if (file_type === 'document') return icons[ext]

      return icons[file_type]
    },
    disableVideo() {
      const { isEncryptionPlus, securityVideoPlayer } = this.storageSetting

      if (!isEncryptionPlus) return false

      if (securityVideoPlayer && !this.isWechat()) return true

      if (!securityVideoPlayer) return true

      return false
    },
    playerId() {
      return 'player' + this.attachment.id + '-' + this.randomId
    }
  },
  methods: {
    isWechat() {
      const ua = navigator.userAgent.toLowerCase();
      if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
      } else {
        return false;
      }
    },
    loadPlayer() {
      if (this.isLoaded) return
      
      // queueLength++

      const initPlayer = (data) => {
        this.isLoaded = true

        return new window.QiQiuYun.Player(
          Object.assign(data)
        );
      }

      Api.getItemDetail({ 
        params: { globalId: this.attachment.global_id } 
      }).then(res => {
        res.data.id = this.playerId
        res.data.language = this.language === 'en' ? this.language : 'zh-CN'
        // playerInitQueue.push(initPlayer.bind(null, res.data))
        initPlayer(res.data)

        // if (playerInitQueue.length === queueLength) {
        //   startInitAllPlayer();
        // }
      })
    },
    previewFile() {
      this.loadPlayer()

      if (!this.isMedia) {
        this.isShow = true
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  .attachment-preview {
    display: flex;
    align-items: center;
    height: 42px;
    padding: 0 12px 0 8px;
    font-size: 14px;
    color: #333;
    background-color: #F7F7F7;
    border-radius: 4px;

    &__icon {
      width: 24px !important;
      height: auto !important;
      margin-bottom: 0 !important;
      margin-right: 8px;
    }

    &__status {
      margin-left: 8px;
      font-size: 12px;
      color: #999;
    }

    .review-img {
      margin-bottom: 0 !important;
      width: vw(30) !important;
      height: vw(30) !important;
    }
  }
</style>
