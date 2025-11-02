"use client";
import React, { useMemo, useState } from "react";
import { http } from "@/lib/http";
import AttributesInput from "@/components/admin/AttributesInput";
import styled from "styled-components";
import Link from "next/link";

const Card = styled.div`
  background: ${({ theme }) => theme.card};
  border: 1px solid ${({ theme }) => theme.border};
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 16px;
`;

type VariantRow = {
  color?: string;
  size?: string;
  sku: string;
  price: number;
  stock: number;
  image_url?: string;
};

export default function NewProductPage() {
  // عام
  const [type, setType] = useState<"simple" | "variable">("variable");
  const [name, setName] = useState("");
  const [slug, setSlug] = useState("");
  const [description, setDescription] = useState("");
  const [imageUrl, setImageUrl] = useState("");
  const [status, setStatus] = useState<"draft"|"active">("active");

  // خصائص
  const [colorName, setColorName] = useState("Color");
  const [sizeName, setSizeName] = useState("Size");
  const [colorCsv, setColorCsv] = useState("Red,Blue,Green");
  const [sizeCsv, setSizeCsv] = useState("S,M,L");

  // متغيرات مولّدة
  const colors = useMemo(
    () => colorCsv.split(",").map(v => v.trim()).filter(Boolean),
    [colorCsv]
  );
  const sizes = useMemo(
    () => sizeCsv.split(",").map(v => v.trim()).filter(Boolean),
    [sizeCsv]
  );

  const generated: VariantRow[] = useMemo(() => {
    if (type === "simple") return [];
    const rows: VariantRow[] = [];
    const base = (name || "PROD").toUpperCase().replace(/\s+/g, "-").slice(0,12);
    for (const c of (colors.length? colors : [undefined])) {
      for (const s of (sizes.length? sizes : [undefined])) {
        const sku = [base, c?.toUpperCase(), s?.toUpperCase()].filter(Boolean).join("-");
        rows.push({ color: c, size: s, sku, price: 0, stock: 0 });
      }
    }
    return rows;
  }, [type, name, colors, sizes]);

  const [variants, setVariants] = useState<VariantRow[]>([]);
  React.useEffect(() => {
    if (type === "variable" && variants.length === 0 && generated.length > 0) {
      setVariants(generated);
    }
  }, [generated, type, variants.length]);

  const updateVariant = (idx: number, patch: Partial<VariantRow>) => {
    setVariants(v => v.map((row, i) => i === idx ? { ...row, ...patch } : row));
  };

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();

    if (!name.trim()) {
      alert("الاسم مطلوب");
      return;
    }

    try {
      if (type === "simple") {
        const payload = {
          type: "simple",
          name,
          slug: slug || name.toLowerCase().replace(/\s+/g, "-"),
          description,
          image_url: imageUrl || undefined,
          price: variants[0]?.price || 0,
          sku: variants[0]?.sku || undefined,
          status
        };
        await http.post("/products", payload);
      } else {
        const payload = {
          type: "variable",
          name,
          slug: slug || name.toLowerCase().replace(/\s+/g, "-"),
          description,
          image_url: imageUrl || "https://cdn.shopify.com/s/files/1/0533/2089/files/placeholder-image.png?v=1681312732",
          status,
          attributes: {
            color: {
              name: colorName || "Color",
              options: colors
            },
            size: {
              name: sizeName || "Size",
              options: sizes
            }
          },
          variants: variants.map(v => ({
            sku: v.sku,
            price: Number.isFinite(v.price) ? v.price : 0,
            stock: Number.isFinite(v.stock) ? v.stock : 0,
            image_url: v.image_url && v.image_url.trim() ? v.image_url : undefined,
            attributes: {
              ...(v.color ? { color: v.color } : {}),
              ...(v.size ? { size: v.size } : {}),
            }
          }))
        };
        await http.post("/products", payload);
      }

      alert("تم إنشاء المنتج بنجاح ✅");
      window.location.href = "/admin/products";
    } catch (err: any) {
      console.error("Create product failed:", err?.response?.data || err);
      const msg = err?.response?.data?.message || err?.message || "فشل إنشاء المنتج";
      alert(msg);
    }
  }

  return (
    <div style={{ padding: 20 }}>
      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 16 }}>
        <h2 style={{ margin: 0 }}>إضافة منتج</h2>
        <Link href="/admin/products" style={{ opacity: 0.8, textDecoration: "underline" }}>
          ← الرجوع للائحة
        </Link>
      </div>

      <form onSubmit={handleSubmit}>
        <Card>
          <h3>المعلومات العامة</h3>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
            <div>
              <label>النوع</label>
              <select
                value={type}
                onChange={(e) => setType(e.target.value as any)}
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              >
                <option value="simple">بسيط</option>
                <option value="variable">متغير</option>
              </select>
            </div>

            <div>
              <label>الحالة</label>
              <select
                value={status}
                onChange={(e) => setStatus(e.target.value as any)}
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              >
                <option value="active">منشور</option>
                <option value="draft">مسودة</option>
              </select>
            </div>

            <div>
              <label>الاسم</label>
              <input
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="اسم المنتج"
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              />
            </div>

            <div>
              <label>Slug</label>
              <input
                value={slug}
                onChange={(e) => setSlug(e.target.value)}
                placeholder="product-slug"
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              />
            </div>

            <div style={{ gridColumn: "1 / -1" }}>
              <label>الوصف</label>
              <textarea
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="وصف واضح وجذاب…"
                rows={4}
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              />
            </div>

            <div style={{ gridColumn: "1 / -1" }}>
              <label>الصورة الرئيسية (URL)</label>
              <input
                value={imageUrl}
                onChange={(e) => setImageUrl(e.target.value)}
                placeholder="https://…"
                style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
              />
              <div style={{ marginTop: 10 }}>
                <img
                  src={imageUrl || "https://cdn.shopify.com/s/files/1/0533/2089/files/placeholder-image.png?v=1681312732"}
                  alt="preview"
                  width={120}
                  height={120}
                  style={{ borderRadius: 8, objectFit: "cover", background: "#1f1f1f", border: "1px solid #333" }}
                />
              </div>
            </div>
          </div>
        </Card>

        {type === "variable" ? (
          <>
            <Card>
              <h3>الخصائص</h3>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label>اسم خاصية اللون</label>
                  <input
                    value={colorName}
                    onChange={(e) => setColorName(e.target.value)}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                  />
                </div>
                <div>
                  <label>اسم خاصية الحجم</label>
                  <input
                    value={sizeName}
                    onChange={(e) => setSizeName(e.target.value)}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                  />
                </div>
              </div>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12, marginTop: 12 }}>
                <AttributesInput
                  label="قائمة الألوان"
                  value={colorCsv}
                  onChange={setColorCsv}
                  hint="مثال: Red,Blue,Green"
                />
                <AttributesInput
                  label="قائمة الأحجام"
                  value={sizeCsv}
                  onChange={setSizeCsv}
                  hint="مثال: S,M,L,XL"
                />
              </div>
              <div style={{ marginTop: 8, opacity: 0.8 }}>
                سيتم توليد المتغيرات أوتوماتيكياً، ويمكن تعديل السعر/المخزون والصورة لكل متغير.
              </div>
            </Card>

            <Card>
              <h3>المتغيرات</h3>
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", borderCollapse: "collapse" }}>
                  <thead>
                    <tr style={{ background: "#111" }}>
                      <th style={{ textAlign: "left", padding: 8 }}>Color</th>
                      <th style={{ textAlign: "left", padding: 8 }}>Size</th>
                      <th style={{ textAlign: "left", padding: 8 }}>SKU</th>
                      <th style={{ textAlign: "left", padding: 8 }}>Price</th>
                      <th style={{ textAlign: "left", padding: 8 }}>Stock</th>
                      <th style={{ textAlign: "left", padding: 8, width: 260 }}>Image URL</th>
                      <th style={{ textAlign: "left", padding: 8 }}>Preview</th>
                    </tr>
                  </thead>
                  <tbody>
                    {(variants.length ? variants : generated).map((v, i) => (
                      <tr key={i} style={{ borderBottom: "1px solid #333" }}>
                        <td style={{ padding: 8 }}>{v.color ?? "-"}</td>
                        <td style={{ padding: 8 }}>{v.size ?? "-"}</td>
                        <td style={{ padding: 8 }}>
                          <input
                            value={v.sku}
                            onChange={(e) => updateVariant(i, { sku: e.target.value })}
                            style={{ width: 180, padding: "6px 8px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                          />
                        </td>
                        <td style={{ padding: 8 }}>
                          <input
                            type="number"
                            value={String(v.price ?? 0)}
                            onChange={(e) => updateVariant(i, { price: Number(e.target.value) })}
                            style={{ width: 100, padding: "6px 8px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                          />
                        </td>
                        <td style={{ padding: 8 }}>
                          <input
                            type="number"
                            value={String(v.stock ?? 0)}
                            onChange={(e) => updateVariant(i, { stock: Number(e.target.value) })}
                            style={{ width: 100, padding: "6px 8px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                          />
                        </td>
                        <td style={{ padding: 8 }}>
                          <input
                            value={v.image_url ?? ""}
                            onChange={(e) => updateVariant(i, { image_url: e.target.value })}
                            placeholder="https://…"
                            style={{ width: "100%", padding: "6px 8px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                          />
                        </td>
                        <td style={{ padding: 8 }}>
                          <img
                            src={v.image_url && v.image_url.trim()
                              ? v.image_url
                              : (imageUrl || "https://cdn.shopify.com/s/files/1/0533/2089/files/placeholder-image.png?v=1681312732")}
                            alt="preview"
                            width={48}
                            height={48}
                            style={{ borderRadius: 8, objectFit: "cover", background: "#1f1f1f", border: "1px solid #333" }}
                          />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Card>
          </>
        ) : (
          <Card>
            <h3>بيانات المنتج البسيط</h3>
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
              <div>
                <label>SKU</label>
                <input
                  value={(variants[0]?.sku) ?? ""}
                  onChange={(e) => setVariants([{ ...(variants[0] || {}), sku: e.target.value }])}
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                />
              </div>
              <div>
                <label>Price</label>
                <input
                  type="number"
                  value={String(variants[0]?.price ?? 0)}
                  onChange={(e) => setVariants([{ ...(variants[0] || {}), price: Number(e.target.value) }])}
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                />
              </div>
              <div>
                <label>Stock</label>
                <input
                  type="number"
                  value={String(variants[0]?.stock ?? 0)}
                  onChange={(e) => setVariants([{ ...(variants[0] || {}), stock: Number(e.target.value) }])}
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid #333", background: "transparent" }}
                />
              </div>
            </div>
          </Card>
        )}

        <div style={{ display: "flex", gap: 12, justifyContent: "flex-end" }}>
          <Link href="/admin/products" style={{ padding: "10px 16px", border: "1px solid #333", borderRadius: 10 }}>
            إلغاء
          </Link>
          <button
            type="submit"
            style={{
              padding: "10px 16px",
              borderRadius: 10,
              border: "1px solid var(--btn-border, #333)",
              background: "var(--btn-bg, #2563eb)",
              color: "#fff",
              cursor: "pointer"
            }}
          >
            حفظ المنتج
          </button>
        </div>
      </form>
    </div>
  );
}
