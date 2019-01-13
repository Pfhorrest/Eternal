Triggers = {}

function Triggers.idle()
  for p in Players() do
    if p.polygon.media then
      p.oxygen = p.oxygen - 4
    end
    if p.oxygen <= 0 then
      p:damage(350, "suffocation")
    end
  end
end