declare interface AgentOptions {
    code: string;
    token: string;
    apiBaseUrl?: string;
    user: {
        id: string;
        name: string;
        avatar?: string;
    };
    variables: Record<string, any>;
    uiIframeSrc: string;
    signalServerUrl?: string;
    bottom?: number;
    right?: number;
    draggable?: boolean;
}

declare class AgentSDK {
    options: AgentOptions;
    private bellhop;
    private signalClient;
    private signalServerUrl;
    private shortcutList;
    private events;
    constructor(options: AgentOptions);
    getDeviceType(): "H5" | "PC";
    private filterReadMessage;
    on(eventName: string, listener: Function): void;
    emit(eventName: string, data?: any): void;
    off(eventName: string, listener: Function): void;
    setVariable(key: string, value: any): void;
    setChatMetadata(value: Record<string, any>): void;
    addShortcut(key: string, content: ShortcutContent): void;
    removeShortcut(key: string): void;
    boot(): void;
    shutdown(): void;
    showIframe(newMessage?: NewMessage): void;
    hideIframe(): void;
    showButton(transition?: boolean): void;
    hideButton(transition?: boolean): void;
    chat(content: string, workflow: Workflow | null): void;
    showReminder(value: Record<string, any>): Promise<void>;
    hideReminder(): void;
}
export default AgentSDK;

declare interface NewMessage {
    content: string;
    workflow: Workflow | null;
}

declare interface ShortcutContent {
    name: string;
    icon: string;
    type: string;
    data: {
        content: string;
    };
}

declare interface Workflow {
    workflow: string;
    inputs: Record<string, any>;
}

export { }
