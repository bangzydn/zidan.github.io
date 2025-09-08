"use client";
import { useState } from "react";

export default function HomePage() {
  const [messages, setMessages] = useState<{ role: "user" | "assistant"; content: string }[]>([]);
  const [input, setInput] = useState("");
  const [loading, setLoading] = useState(false);

  const sendMessage = async () => {
    if (!input.trim()) return;
    const userMsg = { role: "user" as const, content: input };
    setMessages((prev) => [...prev, userMsg]);
    setInput("");
    setLoading(true);
    try {
      const res = await fetch(process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/chat", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: userMsg.content }),
      });
      if (!res.ok) throw new Error(await res.text());
      const data = await res.json();
      const assistantMsg = { role: "assistant" as const, content: data.answer };
      setMessages((prev) => [...prev, assistantMsg]);
    } catch (err: any) {
      setMessages((prev) => [...prev, { role: "assistant", content: `Error: ${err.message ?? String(err)}` }]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="min-h-dvh flex flex-col items-center p-6 gap-4">
      <h1 className="text-2xl font-bold">ClipAI Advisor</h1>
      <div className="w-full max-w-2xl border rounded-lg p-4 flex flex-col gap-3">
        <div className="flex flex-col gap-2 max-h-[60vh] overflow-auto">
          {messages.map((m, i) => (
            <div key={i} className={m.role === "user" ? "text-right" : "text-left"}>
              <span className={m.role === "user" ? "inline-block bg-blue-600 text-white px-3 py-2 rounded-lg" : "inline-block bg-gray-100 px-3 py-2 rounded-lg"}>
                {m.content}
              </span>
            </div>
          ))}
          {loading && <div className="text-sm text-gray-500">Assistant typing...</div>}
        </div>
        <div className="flex gap-2">
          <input
            className="flex-1 border rounded px-3 py-2"
            placeholder="Tanya apa saja tentang kliping..."
            value={input}
            onChange={(e) => setInput(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter") sendMessage();
            }}
          />
          <button className="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50" onClick={sendMessage} disabled={loading}>
            Kirim
          </button>
        </div>
      </div>
    </main>
  );
}
